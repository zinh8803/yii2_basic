<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        $permissions = [
            'brand.viewTrash',
            'category.index',
            'category.view',
            'category.create',
            'category.update',
            'category.softDelete',
            'category.forceDelete',
            'category.restore',

            'brand.viewTrash',
            'brand.index',
            'brand.view',
            'brand.create',
            'brand.update',
            'brand.softDelete',
            'brand.forceDelete',
            'brand.restore',

            'product.index',
            'product.view',
            'product.create',
            'product.update',
            'product.delete',

            'productVariant.index',
            'productVariant.view',
            'productVariant.create',
            'productVariant.update',
            'productVariant.delete',

            'productAttribute.index',
            'productAttribute.view',
            'productAttribute.create',
            'productAttribute.update',
            'productAttribute.delete',

            'post.index',
            'post.view',
            'post.create',
            'post.update',
            'post.delete',
            'post.updateStatus',

            'tag.index',
            'tag.view',
            'tag.create',
            'tag.update',
            'tag.delete',

            'coupon.index',
            'coupon.view',
            'coupon.create',
            'coupon.update',
            'coupon.delete',
            'coupon.checkValid',

            'order.index',
            'order.view',
            'order.create',
            'order.update',
            'order.history',
            'order.updateStatus',

            'cart.view',
            'cart.create',
            'cart.removeItems',
            'cart.clearCart',

            'review.index',
            'review.view',
            'review.update',
            'review.delete',

            'user.index',
            'user.view',
            'user.update',
            'user.delete',

            'file.index',
            'file.view',
        ];

        foreach ($permissions as $permissionName) {
            $permission = $auth->createPermission($permissionName);
            $permission->description = $permissionName;
            $auth->add($permission);
        }

        $user = $auth->createRole('user');
        $auth->add($user);

        $editor = $auth->createRole('editor');
        $auth->add($editor);

        $admin = $auth->createRole('admin');
        $auth->add($admin);

        // permissions for user role
        foreach ([
                     'post.index', 'post.view',
                     'tag.index', 'tag.view',
                     'product.index', 'product.view',
                     'productVariant.index', 'productVariant.view',
                     'productAttribute.index', 'productAttribute.view',
                     'category.index', 'category.view',
                     'brand.index', 'brand.view',
                     'cart.create', 'cart.view', 'cart.clearCart', 'cart.removeItems',
                     'order.view', 'order.create', 'order.history',
                     'review.index', 'review.view',
                     'coupon.index', 'coupon.view', 'coupon.checkValid',
                 ] as $permissionName) {
            $auth->addChild($user, $auth->getPermission($permissionName));
        }

        // permissions for editor role
        foreach ([
                     'post.index',
                     'post.view',
                     'post.create',
                     'post.update',
                     'tag.index',
                     'tag.view',
                     'tag.create',
                     'tag.update',
                 ] as $permissionName) {
            $auth->addChild($editor, $auth->getPermission($permissionName));
        }

        // permissions for admin role
        foreach ($permissions as $permissionName) {
            $auth->addChild($admin, $auth->getPermission($permissionName));
        }

        echo "RBAC init success\n";
    }
}
