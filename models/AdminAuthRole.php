<?php

namespace app\models;

use Yii;

/**
 * 管理员角色模型
 *
 * @property int $id
 * @property string $name 名称
 * @property string $permissions 权限（json）
 * @property string $description 描述
 * @property int $is_trash 是否删除，0=>否，1=>是
 * @property int $status 状态，0=>禁用，1=>启用
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $deleted_at 删除时间
 *
 * @property AdminAuthAssign[] $adminAuthAssigns
 * @property Admin[] $admins
 */
class AdminAuthRole extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_auth_role}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description'], 'trim'],
            [['name', 'description'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],

            [['id', 'is_trash', 'status'], 'integer', 'min' => 0],
            [['name'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 64],
            [['permissions'], 'each', 'rule' => ['integer']],

            [['created_at', 'updated_at', 'deleted_at'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'],

            [['name', 'description', 'permissions'], 'default', 'value' => ''],
            [['is_trash'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 1],

            [['name'], 'unique'],

            [['permissions'], 'each', 'rule' => [
                'exist', 'skipOnError' => true, 'targetClass' => AdminAuthPermission::className(), 'targetAttribute' => ['permissions' => 'id', 'is_trash' => 'is_trash']
            ]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeValidate()
    {
        // JSON转换数组用于each验证
        if (isset($this->permissions) && is_string($this->permissions) && $this->permissions !== '') {
            $this->permissions = json_decode($this->permissions, true);
        }

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // 数组转换JSON入库
        if (isset($this->permissions) && is_array($this->permissions)) {
            $this->permissions = json_encode($this->permissions);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (!$insert) {
            // 删除所有用户权限缓存
            $admins = array_column(Admin::find()->select(['id'])->all(), 'id');

            foreach ($admins as $v) {
                Yii::$app->cache->delete('admin:' . $v . ':permissions');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', '名称'),
            'permissions' => Yii::t('app', '权限（json）'),
            'description' => Yii::t('app', '描述'),
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
    public function getAdminAuthAssigns()
    {
        return $this->hasMany(AdminAuthAssign::className(), ['role_id' => 'id', 'is_trash' => 'is_trash']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getAdmins()
    {
        return $this->hasMany(Admin::className(), ['id' => 'admin_id', 'is_trash' => 'is_trash'])->viaTable('{{%admin_auth_assign}}', ['role_id' => 'id']);
    }
}
