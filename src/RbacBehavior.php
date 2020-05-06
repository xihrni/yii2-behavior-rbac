<?php

namespace xihrni\yii2\behaviors;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\base\InvalidConfigException;
use xihrni\tools\Yii2;

/**
 * RBAC权限认证行为
 *
 * Class RbacBehavior
 * @package app\components\behaviors
 */
class RbacBehavior extends \yii\base\ActionFilter
{
    /**
     * @var bool [$switchOn = true] 开关
     */
    public $switchOn = true;

    /**
     * @var string $userModel 用户模型
     */
    public $userModel;

    /**
     * @var string $roleModel 角色模型
     */
    public $roleModel;

    /**
     * @var string $permissionModel 权限模型
     */
    public $permissionModel;

    /**
     * @var string $assignmentModel 角色分配模型
     */
    public $assignmentModel;

    /**
     * @var array $optional 过滤操作
     */
    public $optional;


    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if ($this->userModel === null) {
            throw new InvalidConfigException(Yii::t('app/error', '{param} must be set.', ['param' => 'userModel']));
        }
        if ($this->roleModel === null) {
            throw new InvalidConfigException(Yii::t('app/error', '{param} must be set.', ['param' => 'roleModel']));
        }
        if ($this->permissionModel === null) {
            throw new InvalidConfigException(Yii::t('app/error', '{param} must be set.', ['param' => 'permissionModel']));
        }
        if ($this->assignmentModel === null) {
            throw new InvalidConfigException(Yii::t('app/error', '{param} must be set.', ['param' => 'assignmentModel']));
        }
    }

    /**
     * @inheritdoc
     * @throws \yii\web\ForbiddenHttpException
     */
    public function beforeAction($action)
    {
        $isPassed = parent::beforeAction($action);
        // 验证父类方法
        if (!$isPassed) {
            return $isPassed;
        }

        // 判断开关
        if (!$this->switchOn) {
            return true;
        }

        // 过滤操作
        if (isset($this->optional) && in_array($action->id, $this->optional)) {
            return true;
        }

        // 获取当前用户拥有的权限
        $userId       = $this->owner->user->id;
        $userModel    = $this->userModel;
        $permissions  = $userModel::getPermissions($userId);
        if (!$permissions) {
            throw new ForbiddenHttpException(Yii::t('app/error', 'You are not authorized to operate.'));
        }

        // 获取当前需要验证的权限
        $moduleId     = Yii2::getFullModuleId($action->controller->module, $ids = []);
        $moduleId     = implode('/', array_reverse($moduleId));
        $controllerId = $action->controller->id;
        $actionId     = $action->id;
        $url          = Yii::$app->request->url;
        $method       = Yii::$app->request->method;

        // 判断用户当前权限是否存在
        foreach ($permissions as $permission) {
            if ($permission['modules'] === $moduleId &&
                $permission['controller'] === $controllerId &&
                $permission['action'] === $actionId &&
                $permission['method'] === $method) {
                // TODO: URL验证和条件验证
                return true;
            }
        }

        throw new ForbiddenHttpException(Yii::t('app/error', 'You are not authorized to operate.'));
    }
}
