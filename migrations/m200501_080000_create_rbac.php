<?php

use yii\db\Migration;

/**
 * Class m200501_080000_create_rbac
 */
class m200501_080000_create_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 创建管理员表
        $this->_createAdminTable();
        // 创建管理员菜单表
        $this->_createAdminAuthMenuTable();
        // 创建管理员角色表
        $this->_createAdminAuthRoleTable();
        // 创建管理员权限表
        $this->_createAdminAuthPermissionTable();
        // 创建管理员角色分配表
        $this->_createAdminAuthAssignTable();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%admin_auth_assign}}');
        $this->dropTable('{{%admin_auth_permission}}');
        $this->dropTable('{{%admin_auth_role}}');
        $this->dropTable('{{%admin_auth_menu}}');
        $this->dropTable('{{%admin}}');
    }


    /* ----private---- */

    /**
     * 创建管理员表
     *
     * @private
     * @return void
     */
    private function _createAdminTable()
    {
        // 创建表
        $this->createTable('{{%admin}}', [
            'id' => $this->primaryKey(11)->unsigned(),
            'username' => $this->string(16)->notNull()->defaultValue('')->comment('用户名')->unique(),
            'password_hash' => $this->string(255)->notNull()->defaultValue('')->comment('加密密码'),
            'password_reset_token' => $this->string(64)->null()->defaultValue(null)->comment('重置密码令牌')->unique(),
            'auth_key' => $this->string(64)->null()->defaultValue(null)->comment('认证密钥')->unique(),
            'access_token' => $this->string(64)->null()->defaultValue(null)->comment('访问令牌')->unique(),
            'mobile' => $this->string(16)->null()->defaultValue(null)->comment('手机号码')->unique(),
            'realname' => $this->string(16)->notNull()->defaultValue('')->comment('真实姓名'),
            'is_trash' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('是否删除，0=>正常，1=>删除'),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1)->comment('状态，0=>禁用，1=>正常'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('创建时间'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('更新时间'),
            'deleted_at' => $this->timestamp()->null()->comment('删除时间'),
            'last_login_at' => $this->timestamp()->null()->comment('最后登录时间'),
            'last_login_ip' => $this->string(16)->notNull()->defaultValue('')->comment('最后登录IP'),
            'allowance' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('请求剩余次数'),
            'allowance_updated_at' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('请求更新时间'),
        ], "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='管理员'");

        // 创建索引
        $this->createIndex('is_trash', '{{%admin}}', 'is_trash');
        $this->createIndex('status', '{{%admin}}', 'status');
    }

    /**
     * 创建管理员后台菜单表
     *
     * @private
     * @return void
     */
    private function _createAdminAuthMenuTable()
    {
        $name  = 'admin_auth_menu';
        $table = '{{%' . $name . '}}';

        // 创建表
        $this->createTable($table, [
            'id' => $this->primaryKey(11)->unsigned(),
            'parent_id' => $this->integer(11)->unsigned()->null()->defaultValue(null)->comment('父ID'),
            'name' => $this->string(32)->notNull()->defaultValue('')->comment('名称'),
            'is_trash' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('是否删除，0=>正常，1=>删除'),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1)->comment('状态，0=>禁用，1=>正常'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('创建时间'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('更新时间'),
            'deleted_at' => $this->timestamp()->null()->comment('删除时间'),
        ], "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='管理员后台菜单'");

        // 创建索引
        $this->createIndex('is_trash', $table, 'is_trash');
        $this->createIndex('status', $table, 'status');
        $this->createIndex('name', $table, ['parent_id', 'name', 'is_trash'], true);

        // 添加外键
        $this->addForeignKey($name . '_fk_parent_id', $table, 'parent_id', $table, 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * 创建管理员角色表
     *
     * @private
     * @return void
     */
    private function _createAdminAuthRoleTable()
    {
        $name  = 'admin_auth_role';
        $table = '{{%' . $name . '}}';

        // 创建表
        $this->createTable($table, [
            'id' => $this->primaryKey(11)->unsigned(),
            'name' => $this->string(32)->notNull()->defaultValue('')->comment('名称')->unique(),
            'permissions' => $this->text()->notNull()->comment('权限（json）'),
            'description' => $this->string(64)->notNull()->defaultValue('')->comment('描述'),
            'is_trash' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('是否删除，0=>正常，1=>删除'),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1)->comment('状态，0=>禁用，1=>正常'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('创建时间'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('更新时间'),
            'deleted_at' => $this->timestamp()->null()->comment('删除时间'),
        ], "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='管理员角色'");

        // 创建索引
        $this->createIndex('is_trash', $table, 'is_trash');
        $this->createIndex('status', $table, 'status');
    }

    /**
     * 创建管理员权限表
     *
     * @private
     * @return void
     */
    private function _createAdminAuthPermissionTable()
    {
        $name  = 'admin_auth_permission';
        $table = '{{%' . $name . '}}';

        // 创建表
        $this->createTable($table, [
            'id' => $this->primaryKey(11)->unsigned(),
            'menu_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('菜单ID'),
            'title' => $this->string(32)->notNull()->defaultValue('')->comment('标题'),
            'modules' => $this->string(128)->notNull()->defaultValue('')->comment('模块'),
            'controller' => $this->string(32)->notNull()->defaultValue('')->comment('控制器'),
            'action' => $this->string(32)->notNull()->defaultValue('')->comment('操作'),
            'name' => $this->string(255)->notNull()->defaultValue('')->comment('名称（完整路径）'),
            'method' => $this->string(8)->notNull()->defaultValue('')->comment('方法'),
            'condition' => $this->text()->notNull()->comment('条件（json）'),
            'is_trash' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('是否删除，0=>正常，1=>删除'),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1)->comment('状态，0=>禁用，1=>正常'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('创建时间'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('更新时间'),
            'deleted_at' => $this->timestamp()->null()->comment('删除时间'),
        ], "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='管理员权限'");

        // 创建索引
        $this->createIndex('is_trash', $table, 'is_trash');
        $this->createIndex('status', $table, 'status');
        $this->createIndex('title', $table, ['menu_id', 'title', 'is_trash'], true);
        $this->createIndex('name', $table, ['modules', 'controller', 'action', 'name', 'method', 'is_trash'], true);

        // 添加外键
        $this->addForeignKey($name . '_fk_menu_id', $table, 'menu_id', '{{%admin_auth_menu}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * 创建管理员角色分配表
     *
     * @private
     * @return void
     */
    private function _createAdminAuthAssignTable()
    {
        $name  = 'admin_auth_assign';
        $table = '{{%' . $name . '}}';

        // 创建表
        $this->createTable($table, [
            'admin_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('管理员ID'),
            'role_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0)->comment('角色ID'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('创建时间'),
        ], "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='管理员角色分配'");

        // 添加主键
        $this->addPrimaryKey('id', $table, ['admin_id', 'role_id']);

        // 添加外键
        $this->addForeignKey($name . '_fk_admin_id', $table, 'admin_id', '{{%admin}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey($name . '_fk_role_id', $table, 'role_id', '{{%admin_auth_role}}', 'id', 'CASCADE', 'CASCADE');
    }
}
