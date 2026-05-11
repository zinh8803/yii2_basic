<?php

namespace app\controllers;

use app\models\CartItems;
use app\models\Carts;
use app\models\ProductVariants;
use app\models\forms\Cart\AddToCartForm;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CartController implements the CRUD actions for carts model.
 */
class CartController extends Controller
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
                        'add-item' => ['POST'],
                        'delete' => ['POST'],
                        'remove-item' => ['POST'],
                        'update-item' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all carts models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Carts::find(),
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
     * Displays a single carts model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new carts model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AddToCartForm();

        if ($this->request->isPost) {
            $model->load($this->request->post());

            if ($model->validate()) {
                $cart = $this->addItemFromForm($model);

                return $this->redirect(['view', 'id' => $cart->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing carts model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        Yii::$app->session->setFlash('info', 'Use cart item actions to update quantities.');

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Deletes an existing carts model.
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
     * Finds the carts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return carts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Carts::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionAddItem()
    {
        $form = new AddToCartForm();
        $form->load($this->request->post());

        if (!$form->validate()) {
            return $this->redirect(['index']);
        }

        $cart = $this->addItemFromForm($form);

        return $this->redirect(['view', 'id' => $cart->id]);
    }

    public function actionUpdateItem($id)
    {
        $userId = (int) $this->request->post('user_id');
        $cart = $this->getOrCreateCartByUserId($userId);

        $item = CartItems::findOne([
            'id' => $id,
            'cart_id' => $cart->id,
        ]);

        if ($item === null) {
            throw new NotFoundHttpException('Cart item not found.');
        }

        $quantity = (int) $this->request->post('quantity');
        if ($quantity < 1) {
            $item->delete();
        } else {
            $item->quantity = $quantity;
            $item->save();
        }

        $this->recalculateTotal($cart);

        return $this->redirect(['view', 'id' => $cart->id]);
    }

    public function actionRemoveItem($id)
    {
        $userId = (int) $this->request->post('user_id');
        $cart = $this->getOrCreateCartByUserId($userId);

        $item = CartItems::findOne([
            'id' => $id,
            'cart_id' => $cart->id,
        ]);

        if ($item === null) {
            throw new NotFoundHttpException('Cart item not found.');
        }

        $item->delete();
        $this->recalculateTotal($cart);

        return $this->redirect(['view', 'id' => $cart->id]);
    }

    private function getOrCreateCartByUserId(int $userId)
    {
        $cart = Carts::findOne(['user_id' => $userId]);
        if ($cart !== null) {
            return $cart;
        }

        $cart = new Carts();
        $cart->user_id = $userId;
        $cart->total = 0;
        $cart->save(false);

        return $cart;
    }

    private function recalculateTotal(Carts $cart)
    {
        $total = 0;

        foreach ($cart->cartItems as $item) {
            $total += $item->price * $item->quantity;
        }

        $cart->total = $total;
        $cart->save(false, ['total']);
    }

    private function addItemFromForm(AddToCartForm $form)
    {
        $variant = ProductVariants::findOne(['id' => $form->product_variant_id]);
        if ($variant === null || (int) $variant->product_id !== (int) $form->product_id) {
            throw new NotFoundHttpException('Product variant not found.');
        }

        $cart = $this->getOrCreateCartByUserId((int) $form->user_id);

        $item = CartItems::findOne([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
        ]);

        $price = $variant->sale_price !== null ? $variant->sale_price : $variant->price;

        if ($item === null) {
            $item = new CartItems();
            $item->cart_id = $cart->id;
            $item->product_id = $form->product_id;
            $item->product_variant_id = $variant->id;
            $item->quantity = $form->quantity;
            $item->price = $price;
        } else {
            $item->quantity += $form->quantity;
            $item->price = $price;
        }

        $item->save();
        $this->recalculateTotal($cart);

        return $cart;
    }
}
