<?php

namespace app\controllers;

use app\models\Categories;
use app\models\Products;
use app\controllers\BaseController;
class TestController extends BaseController
{
    public function actionIndex()
    {
        $products = Products::find()
            ->joinWith(['category', 'brand'])

            ->asArray(true)
            ->all();
        return $this->json(true, $products, 'Products retrieved successfully');
    }
    //find products by category_id
    public function actionByCategory($category_id)
    {
        $products = Products::find()
            ->joinWith(['category', 'brand'])
            ->with(['productVariants', 'productAttributes', 'productAttributes.attributeValues'])
            ->where([
                'products.category_id' => $category_id,
            ])
            ->asArray(true)
            ->all();
        if (!$products) {
            return $this->json(false, null, 'Products not found');
        }
        return $this->json(true, $products, 'Products retrieved successfully');
    }

    //find products by product_id
    public function actionView($id)
    {
        $product = Products::find()
            ->joinWith(['category', 'brand'])
            ->with(['productVariants', 'productAttributes', 'productAttributes.attributeValues'])
            ->where([
                'products.id' => $id,
            ])
            ->one();
        if (!$product) {
            return $this->json(false, null, 'Product not found');
        }
        return $this->json(true, $product, 'Product retrieved successfully');
    }



    // Search product name
    public function actionSearch($query)
    {
        $products = Products::find()
            ->joinWith(['category', 'brand'])
            ->with(['productVariants', 'productAttributes', 'productAttributes.attributeValues'])
            ->where([
                'like',
                'products.name',
                $query,
            ])
            ->asArray(true)
            ->all();
        if (!$products) {
            return $this->json(false, null, 'Products not found');
        }
        return $this->json(true, $products, 'Products retrieved successfully');
    }


    // Get product with variants and attributes
    public function actionVariant()
    {
        $product = Products::find()
            ->joinWith(['category', 'brand'])
            ->with(['productVariants', 'productAttributes', 'productAttributes.attributeValues'])
            ->limit(5)
            ->offset(0)
            ->asArray(true)
            ->all();
        if (!$product) {
            return $this->json(false, null, 'Product not found');
        }
        return $this->json(true, $product, 'Product retrieved successfully');
    }

    // Get categories with products status 1 and product count less than 3
    public function actionCategoryProduct()
    {
        $category = Categories::find()
            ->joinWith([
                'products' => function ($query) {
                    $query->andWhere(['products.status' => 1]);
                },
            ])
            ->groupBy('categories.id')
            ->having(['<', 'COUNT(products.id)', 3])
            ->asArray()
            ->all();
        return $this->json(true, $category, 'Categories with products retrieved successfully');
    }
    public function actionCategories()
    {
        $categories = Categories::find()
            ->where(['parent_id' => null])
            ->with('children')
            ->all();
        return $this->json(true, $categories, 'Categories retrieved successfully');
    }
}
