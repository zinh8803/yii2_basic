<?php

namespace app\controllers;

use app\models\forms\Order\CreateOrderForm;
use app\models\OrderItems;
use app\models\Orders;
use app\models\Payments;
use app\models\Products;
use app\models\ProductVariants;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * OrderController implements the CRUD actions for Orders model.
 */
class OrderController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Orders models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Orders::find(),
            /*
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            */
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Orders model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModelWithItems($id),
        ]);
    }

    /**
     * Creates a new Orders model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $form = new CreateOrderForm();

        if ($this->request->isPost) {
            $form->load($this->request->post());

            if (!$form->validate()) {
                return $this->render('create', [
                    'model' => $form,
                ]);
            }

            $transaction = Yii::$app->db->beginTransaction();

            try {
                $order = $this->buildOrderFromForm($form);
                $order->total = 0;

                if ($order->save()) {
                    $items = $this->buildItemsFromFields($form);
                    if (empty($items)) {
                        $form->addError('item_product_id', 'Order item is required.');
                        $transaction->rollBack();
                        return $this->render('create', [
                            'model' => $form,
                        ]);
                    }

                    $total = $this->createOrderItems($order, $form, $items);
                    if ($total === false) {
                        $transaction->rollBack();
                        return $this->render('create', [
                            'model' => $form,
                        ]);
                    }

                    $order->total = $total + (float) $order->shipping_fee - (float) $order->discount_amount;
                    $order->save(false, ['total']);

                    $this->createOrUpdatePayment($order, $form);

                    $transaction->commit();
                    return $this->redirect(['view', 'id' => $order->id]);
                }

                $this->addOrderErrorsToForm($order, $form);
                $transaction->rollBack();
                return $this->render('create', [
                    'model' => $form,
                ]);
            } catch (\Throwable $exception) {
                $transaction->rollBack();
                throw $exception;
            }
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * Updates an existing Orders model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $order = $this->findModel($id);
        $form = $this->buildFormFromOrder($order);

        if ($this->request->isPost) {
            $form->load($this->request->post());

            if (!$form->validate()) {
                return $this->render('update', [
                    'model' => $form,
                ]);
            }

            $transaction = Yii::$app->db->beginTransaction();

            try {
                $order = $this->applyFormToOrder($order, $form);
                $order->total = 0;

                if ($order->save()) {
                    OrderItems::deleteAll(['order_id' => $order->id]);

                    $items = $this->buildItemsFromFields($form);
                    if (empty($items)) {
                        $form->addError('item_product_id', 'Order item is required.');
                        $transaction->rollBack();
                        return $this->render('update', [
                            'model' => $form,
                        ]);
                    }

                    $total = $this->createOrderItems($order, $form, $items);
                    if ($total === false) {
                        $transaction->rollBack();
                        return $this->render('update', [
                            'model' => $form,
                        ]);
                    }

                    $order->total = $total + (float) $order->shipping_fee - (float) $order->discount_amount;
                    $order->save(false, ['total']);

                    $this->createOrUpdatePayment($order, $form);

                    $transaction->commit();
                    return $this->redirect(['view', 'id' => $order->id]);
                }

                $this->addOrderErrorsToForm($order, $form);
                $transaction->rollBack();
            } catch (\Throwable $exception) {
                $transaction->rollBack();
                throw $exception;
            }
        }

        return $this->render('update', [
            'model' => $form,
        ]);
    }

    /**
     * Deletes an existing Orders model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Orders model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Orders the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Orders::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function findModelWithItems($id)
    {
        $model = Orders::find()
            ->with(['orderItems'])
            ->where(['id' => $id])
            ->one();

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    private function createOrderItems(Orders $order, CreateOrderForm $form, array $items)
    {
        $total = 0;

        foreach ($items as $item) {
            if ($item['quantity'] < 1) {
                continue;
            }

            $product = Products::findOne(['id' => $item['product_id']]);
            $variant = ProductVariants::findOne(['id' => $item['variant_id']]);

            if ($product === null || $variant === null || (int) $variant->product_id !== (int) $product->id) {
                $form->addError('item_product_id', 'Invalid product or variant.');
                return false;
            }

            $price = $variant->sale_price !== null ? $variant->sale_price : $variant->price;

            $orderItem = new OrderItems();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $product->id;
            $orderItem->variant_id = $variant->id;
            $orderItem->product_name = $product->name;
            $orderItem->variant_name = $variant->name;
            $orderItem->sku = $variant->sku;
            $orderItem->quantity = $item['quantity'];
            $orderItem->price = $price;
            if (!$orderItem->save()) {
                $form->addError('item_product_id', 'Failed to save order item.');
                return false;
            }

            $total += $price * $item['quantity'];
        }

        return $total;
    }

    private function buildItemsFromFields(CreateOrderForm $form)
    {
        $items = [];
        $count = max(
            count($form->item_product_id ?? []),
            count($form->item_variant_id ?? []),
            count($form->item_quantity ?? [])
        );
        for ($i = 0; $i < $count; $i++) {
            $pid = $form->item_product_id[$i] ?? null;
            $vid = $form->item_variant_id[$i] ?? null;
            $qty = $form->item_quantity[$i] ?? null;
            if ($pid && $vid && $qty) {
                $items[] = [
                    'product_id' => (int) $pid,
                    'variant_id' => (int) $vid,
                    'quantity' => (int) $qty,
                ];
            }
        }
        return $items;
    }

    private function createOrUpdatePayment(Orders $order, CreateOrderForm $form)
    {
        $payment = Payments::findOne(['order_id' => $order->id]);
        if ($payment === null) {
            $payment = new Payments();
            $payment->order_id = $order->id;
            $payment->transaction_id = strtoupper(Yii::$app->security->generateRandomString(12));
            $payment->idempotency_key = strtoupper(Yii::$app->security->generateRandomString(16));
            $payment->created_at = time();
        }

        $payment->amount = $order->total;
        $payment->payment_method = $form->payment_method;
        $payment->status = $form->payment_status;
        $payment->payment_status = $form->payment_status;
        $payment->updated_at = time();
        $payment->save();
    }

    private function buildOrderFromForm(CreateOrderForm $form)
    {
        $order = new Orders();

        return $this->applyFormToOrder($order, $form);
    }

    private function applyFormToOrder(Orders $order, CreateOrderForm $form)
    {
        $order->user_id = $form->user_id;
        $order->email = $form->email;
        $order->receiver_name = $form->receiver_name;
        $order->receiver_phone = $form->receiver_phone;
        $order->receiver_address = $form->receiver_address;
        $order->note = $form->note;
        $order->is_discounted = $form->is_discounted;
        $order->shipping_fee = $form->shipping_fee;
        $order->discount_amount = $form->discount_amount;
        $order->payment_method = $form->payment_method;
        $order->payment_status = $form->payment_status;
        $order->status = $form->status;

        return $order;
    }

    private function buildFormFromOrder(Orders $order)
    {
        $form = new CreateOrderForm();
        $form->user_id = $order->user_id;
        $form->email = $order->email;
        $form->receiver_name = $order->receiver_name;
        $form->receiver_phone = $order->receiver_phone;
        $form->receiver_address = $order->receiver_address;
        $form->note = $order->note;
        $form->is_discounted = $order->is_discounted;
        $form->shipping_fee = $order->shipping_fee;
        $form->discount_amount = $order->discount_amount;
        $form->payment_method = $order->payment_method;
        $form->payment_status = $order->payment_status;
        $form->status = $order->status;

        $form->item_product_id = [];
        $form->item_variant_id = [];
        $form->item_quantity = [];
        foreach ($order->orderItems as $item) {
            $form->item_product_id[] = $item->product_id;
            $form->item_variant_id[] = $item->variant_id;
            $form->item_quantity[] = $item->quantity;
        }

        return $form;
    }

    private function addOrderErrorsToForm(Orders $order, CreateOrderForm $form)
    {
        foreach ($order->getErrors() as $attribute => $messages) {
            foreach ($messages as $message) {
                $form->addError($attribute, $message);
            }
        }
    }
}
