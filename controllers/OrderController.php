<?php

namespace app\controllers;

use app\models\forms\Order\CreateOrderForm;
use app\models\OrderItems;
use app\models\Orders;
use app\models\Payments;
use app\models\Products;
use app\models\ProductVariants;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class OrderController extends BaseController
{
    public $modelClass = 'app\\models\\Orders';

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

    public function actionIndex()
    {
        $query = Orders::find();
        $data = $this->paginate($query);
        return $this->json(true, $data, 'Orders retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModelWithItems($id);
        return $this->json(true, $model, 'Order retrieved successfully');
    }

    public function actionCreate()
    {
        $form = new CreateOrderForm();
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
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
                    return $this->json(false, $form->errors, 'Validation failed', 422);
                }

                $total = $this->createOrderItems($order, $form, $items);
                if ($total === false) {
                    $transaction->rollBack();
                    return $this->json(false, $form->errors, 'Validation failed', 422);
                }

                $order->total = $total + (float) $order->shipping_fee - (float) $order->discount_amount;
                $order->save(false, ['total']);

                $this->createOrUpdatePayment($order, $form);

                $transaction->commit();
                $responseModel = $this->findModelWithItems($order->id);
                return $this->json(true, $responseModel, 'Order created successfully', 201);
            }

            $this->addOrderErrorsToForm($order, $form);
            $transaction->rollBack();
            return $this->json(false, $form->errors, 'Validation failed', 422);
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }
    }

    public function actionUpdate($id)
    {
        $order = $this->findModel($id);
        $form = $this->buildFormFromOrder($order);
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
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
                    return $this->json(false, $form->errors, 'Validation failed', 422);
                }

                $total = $this->createOrderItems($order, $form, $items);
                if ($total === false) {
                    $transaction->rollBack();
                    return $this->json(false, $form->errors, 'Validation failed', 422);
                }

                $order->total = $total + (float) $order->shipping_fee - (float) $order->discount_amount;
                $order->save(false, ['total']);

                $this->createOrUpdatePayment($order, $form);

                $transaction->commit();
                $responseModel = $this->findModelWithItems($order->id);
                return $this->json(true, $responseModel, 'Order updated successfully');
            }

            $this->addOrderErrorsToForm($order, $form);
            $transaction->rollBack();
            return $this->json(false, $form->errors, 'Validation failed', 422);
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->json(true, null, 'Order deleted successfully');
    }

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
