<?php

namespace xihrni\yii2\behaviors;

/**
 * 管理员模型接口
 *
 * Interface AdminInterface
 * @package xihrni\yii2\behaviors
 */
interface AdminInterface
{
    /**
     * 获取权限列表
     *
     * @param  int $id 管理员ID
     * @return array 权限列表
     */
    public static function getPermissions($id);
}
