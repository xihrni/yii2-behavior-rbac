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

