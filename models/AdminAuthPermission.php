<?php

namespace app\models;

use Yii;

/**
 * 管理员权限模型
 *
 * @property int $id
 * @property int $menu_id 菜单ID
 * @property string $title 标题
 * @property string $modules 模块
 * @property string $controller 控制器
 * @property string $action 操作
 * @property string $name 名称
 * @property string $method 方法
 * @property string $condition 条件（json）
 * @property int $is_trash 是否删除，0=>否，1=>是
 * @property int $status 状态，0=>禁用，1=>启用
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $deleted_at 删除时间
 *
 * @property AdminAuthMenu $menu
 */
class AdminAuthPermission extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_auth_permission}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'menu_id', 'is_trash', 'status'], 'integer', 'min' => 0],

            [['method'], 'string', 'max' => 8],
            [['title', 'controller', 'action'], 'string', 'max' => 32],
            [['modules'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 128],
            [['iview_admin_path', 'iview_admin_name', 'iview_admin_meta', 'iview_admin_component'], 'string', 'max' => 255],
            [['condition'], 'string'],

            [['created_at', 'updated_at', 'deleted_at'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'],

            [['title', 'modules', 'controller', 'action', 'name', 'method', 'condition', 'iview_admin_path', 'iview_admin_name', 'iview_admin_meta', 'iview_admin_component'], 'default', 'value' => ''],
            [['menu_id', 'is_trash'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 1],

            [['title'], 'unique', 'targetAttribute' => ['menu_id', 'title', 'is_trash'], 'message' => '菜单ID 与 标题 的值已经被占用了。'],
            [['modules', 'controller', 'action', 'method'], 'unique', 'targetAttribute' => ['modules', 'controller', 'action', 'method', 'is_trash'], 'message' => '模块, 控制器, 操作 与 方法 的值已经被占用了。',],

            [['menu_id'], 'exist', 'skipOnError' => true, 'targetClass' => AdminAuthMenu::className(), 'targetAttribute' => ['menu_id' => 'id', 'is_trash' => 'is_trash']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'menu_id' => Yii::t('app', '菜单ID'),
            'title' => Yii::t('app', '标题'),
            'modules' => Yii::t('app', '模块'),
            'controller' => Yii::t('app', '控制器'),
            'action' => Yii::t('app', '操作'),
            'name' => Yii::t('app', '名称'),
            'method' => Yii::t('app', '方法'),
            'condition' => Yii::t('app', '条件（json）'),
            'is_trash' => Yii::t('app', '是否删除，0=>否，1=>是'),
            'status' => Yii::t('app', '状态，0=>禁用，1=>启用'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'deleted_at' => Yii::t('app', '删除时间'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(AdminAuthMenu::className(), ['id' => 'menu_id', 'is_trash' => 'is_trash']);
    }
}
