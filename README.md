# Yii2 RBAC权限认证行为

## Introduction
用于基于 RBAC 的权限认证

## Install
```composer
$ composer require xihrni/yii2-behavior-rbac
```

## Usage
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

