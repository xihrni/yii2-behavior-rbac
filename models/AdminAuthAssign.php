<?php

namespace app\models;

use Yii;

/**
 * 管理员角色分配模型
 *
 * @property int $admin_id 管理员ID
 * @property int $role_id 角色ID
 * @property string $created_at 创建时间
 *
 * @property Admin $admin
 * @property AdminAuthRole $role
 */
class AdminAuthAssign extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_auth_assign}}';
    }

    /**
     * 行为
     */
    public function behaviors()
    {
        // 重写父级行为
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id', 'role_id'], 'required'],

            [['admin_id', 'role_id'], 'integer', 'min' => 0],

            [['created_at'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'],

            [['admin_id', 'role_id'], 'default', 'value' => 0],

            [['admin_id', 'role_id'], 'unique', 'targetAttribute' => ['admin_id', 'role_id']],

            [['admin_id'], 'exist', 'skipOnError' => true, 'targetClass' => Admin::className(), 'targetAttribute' => ['admin_id' => 'id']],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => AdminAuthRole::className(), 'targetAttribute' => ['role_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'admin_id' => Yii::t('app', '管理员ID'),
            'role_id' => Yii::t('app', '角色ID'),
            'created_at' => Yii::t('app', '创建时间'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdmin()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(AdminAuthRole::className(), ['id' => 'role_id']);
    }
}
