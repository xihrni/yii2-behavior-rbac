<?php

namespace app\models;

use Yii;

/**
 * 管理员模型
 *
 * @property int $id
 * @property string $username 用户名
 * @property string $password_hash 加密密码
 * @property string $password_reset_token 重置密码令牌
 * @property string $auth_key 认证密钥
 * @property string $access_token 访问令牌
 * @property string $mobile 手机号码
 * @property string $realname 真实姓名
 * @property int $is_trash 是否删除，0=>否，1=>是
 * @property int $status 状态，0=>禁用，1=>启用
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $deleted_at 删除时间
 * @property string $last_login_at 最后登录时间
 * @property string $last_login_ip 最后登录IP
 * @property int $allowance 请求剩余次数
 * @property string $allowance_updated_at 请求更新时间
 *
 * @property AdminAuthAssign[] $adminAuthAssigns
 * @property AdminAuthRole[] $roles
 */
class Admin extends BaseActiveRecord implements \xihrni\yii2\behaviors\AdminInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'is_trash', 'status'], 'integer', 'min' => 0],

            [['username', 'mobile', 'realname'], 'string', 'max' => 16],
            [['password_hash'], 'string', 'max' => 255],
            [['password_reset_token', 'auth_key', 'access_token'], 'string', 'max' => 64],

            [['created_at', 'updated_at', 'deleted_at', 'last_login_at'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'],

            [['last_login_ip'], 'ip'],
            [['mobile'], 'match', 'pattern' => '/^1([356789]{1})\d{9}$/'],

            [['password_reset_token', 'auth_key', 'access_token', 'mobile'], 'default', 'value' => null],
            [['username', 'password_hash', 'realname'], 'default', 'value' => ''],
            [['is_trash', 'allowance', 'allowance_updated_at'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 1],

            [['username'], 'unique'],
            [['access_token'], 'unique'],
            [['auth_key'], 'unique'],
            [['password_reset_token'], 'unique'],
            [['mobile'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        $isValid = parent::beforeSave($insert);

        if (!$isValid) {
            return $isValid;
        }

        // 更新操作
        if (!$insert) {
            // 不允许更新的字段
            $this->username = $this->oldAttributes['username'];
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', '用户名'),
            'password_hash' => Yii::t('app', '加密密码'),
            'password_reset_token' => Yii::t('app', '重置密码令牌'),
            'auth_key' => Yii::t('app', '认证密钥'),
            'access_token' => Yii::t('app', '访问令牌'),
            'mobile' => Yii::t('app', '手机号码'),
            'realname' => Yii::t('app', '真实姓名'),
            'is_trash' => Yii::t('app', '是否删除，0=>否，1=>是'),
            'status' => Yii::t('app', '状态，0=>禁用，1=>启用'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'deleted_at' => Yii::t('app', '删除时间'),
            'last_login_at' => Yii::t('app', '最后登录时间'),
            'last_login_ip' => Yii::t('app', '最后登录IP'),
            'allowance' => Yii::t('app', '请求剩余次数'),
            'allowance_updated_at' => Yii::t('app', '请求更新时间'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdminAuthAssigns()
    {
        return $this->hasMany(AdminAuthAssign::className(), ['admin_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getRoles()
    {
        return $this->hasMany(AdminAuthRole::className(), ['id' => 'role_id', 'is_trash' => 'is_trash'])
            ->viaTable('{{%admin_auth_assign}}', ['admin_id' => 'id'])
            ->where(['status' => 1]);
    }


    /* ----static---- */

    /**
     * 获取权限
     *
     * @param  int $id 主键ID
     * @return array 权限列表
     */
    public static function getPermissions($id)
    {
        return Yii::$app->cache->getOrSet('admin:' . $id . ':permissions', function () use ($id) {
            $roles = AdminAuthAssign::find()
                ->alias('assign')
                ->select(['role.permissions as permissions'])
                ->where(['assign.admin_id' => $id])
                ->leftJoin(AdminAuthRole::tableName() . 'as role', 'assign.role_id = role.id and role.status = 1')
                ->asArray()
                ->all();

            $permissionIds = array_column($roles, 'permissions'); // 提取权限列
            $permissionIds = array_filter($permissionIds); // 过滤空数据
            $permissionIds = array_map('json_decode', $permissionIds); // JSON转数组
            $permissionIds = array_reduce($permissionIds, 'array_merge', []); // 二维数组合并成一位数组
            $permissionIds = array_unique($permissionIds); // 数组去重
            $permission    = AdminAuthPermission::find()
                ->cache()
                ->select(['modules', 'controller', 'action', 'name', 'method', 'condition'])
                ->where(['id' => $permissionIds])
                ->asArray()
                ->all();

            return $permission;
        });
    }
}
