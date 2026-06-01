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
            'category.index',
            'category.view',
            'category.create',
            'category.update',
            'category.delete',

            'brand.index',
            'brand.view',
            'brand.create',
            'brand.update',
            'brand.delete',

            'product.index',
            'product.view',
            'product.create',
            'product.update',
            'product.delete',

            'post.index',
            'post.view',
            'post.create',
            'post.update',
            'post.delete',

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

            'order.index',
            'order.view',
            'order.update',
            'order.updateStatus',

            'review.index',
            'review.view',
            'review.update',
            'review.delete',

            'user.index',
            'user.view',
            'user.update',
            'user.delete',
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
        foreach (['post.index', 'post.view', 'product.index', 'product.view', 'category.index', 'category.view'] as $permissionName) {
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
