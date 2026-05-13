<?php

namespace app\controllers;

use app\models\Categories;
use app\models\Products;
use app\controllers\BaseController;
use app\models\response\ProductResponse;

class TestController extends BaseController
{
    public function actionIndex()
    {
        $products = ProductResponse::find();
        //->joinWith(['category', 'brand'])
        // ->asArray(true)
        //->all();
        $result = $this->paginate($products,5);
        return $this->json(true, $result, 'Products retrieved successfully');
    }
    //find products by category_id
    public function actionByCategory($category_id)
    {
        $products = ProductResponse::find()
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
        $product = ProductResponse::find()
            ->with(['productVariants', 'productAttributes', 'productAttributes.attributeValues'])
            ->joinWith(['category', 'brand'])
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
        $products = ProductResponse::find()
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
        $product = ProductResponse::find()
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

    public function actionByBrands($brand_id)
    {
        $products = ProductResponse::find()
            ->active()
            // ->byBrand($brand_id)
            //  ->asArray(true)
            ->all();
        if (!$products) {
            return $this->json(false, null, 'Products not found');
        }
        return $this->json(true, $products, 'Products retrieved successfully');
    }



}
