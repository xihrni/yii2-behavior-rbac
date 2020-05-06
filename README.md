# Yii2 RBAC权限认证行为

## Introduction
用于基于 RBAC 的权限认证

## Install
```composer
$ composer require xihrni/yii2-behavior-rbac
```

## Usage
### Database
使用 Yii2 的迁移来生成数据库中的相关表
```php
yii migrate --migrationPath=@vendor/xihrni/yii2-behavior-rbac/migrations
```

### Model
复制已准备好的模型到项目中，目录为：`@vendor/xihrni/yii2-behavior-rbac/models`，需要注意 `admin` 管理员模型需要实现 `AdminInterface` 接口方法
```php
interface AdminInterface
{
    public static function getPermissions($id);
}
```

### Controller
```php
<?php

namespace app\controllers;

use xihrni\yii2\behaviors\RbacBehavior;

class IndexController extends \yii\web\Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'rbac' => [
                'class' => RbacBehavior::className(),
                'switchOn' => true,
                'userModel' => 'app\models\Admin',
                'roleModel' => 'app\models\AuthRoleModel',
                'permissionModel' => 'app\models\AuthPermissionModel',
                'assignmentModel' => 'app\models\AuthAssignmentModel',
            ],
        ]);
    }
}
```

