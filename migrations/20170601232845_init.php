<?php

use Phpmig\Migration\Migration;

class Init extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $container = $this->getContainer();
        $connection = $container['db'];

        $connection->exec("
            -- ----------------------------
            --  Table structure for `goods`
            -- ----------------------------
            DROP TABLE IF EXISTS `goods`;
            CREATE TABLE `goods` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `name` varchar(255) NOT NULL COMMENT '商品名称',
              `category_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类id',
              `small_picture` varchar(255) NOT NULL DEFAULT '' COMMENT '小图',
              `medium_picture` varchar(255) NOT NULL DEFAULT '' COMMENT '中图',
              `large_picture` varchar(255) NOT NULL DEFAULT '' COMMENT '大图',
              `spell_code` varchar(255) NOT NULL COMMENT '拼音编码',
              `spec` varchar(64) NOT NULL COMMENT '规格',
              `unit` varchar(64) NOT NULL COMMENT '单位',
              `ingredient` varchar(1024) NOT NULL COMMENT '原料',
              `cost_price` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '成本价',
              `sale_price` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '售价',
              `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序值',
              `provider_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '供应商id',
              `about` text COMMENT '简介',
              `group_code` varchar(64) NOT NULL DEFAULT 'product' COMMENT '商品组别',
              `owner_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户id',
              `owner_region_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属区域id',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='商品表';

            -- ----------------------------
            --  Table structure for `goods_category`
            -- ----------------------------
            DROP TABLE IF EXISTS `goods_category`;
            CREATE TABLE `goods_category` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `name` varchar(255) NOT NULL COMMENT '分类名称',
              `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级id',
              `group_code` varchar(64) NOT NULL DEFAULT 'product' COMMENT '商品组别',
              `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序值',
              `depth` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '层级',
              `owner_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户id',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='商品分类表';

            -- ----------------------------
            --  Table structure for `log`
            -- ----------------------------
            DROP TABLE IF EXISTS `log`;
            CREATE TABLE `log` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` int(10) unsigned NOT NULL DEFAULT '0',
              `module` varchar(32) NOT NULL,
              `action` varchar(32) NOT NULL,
              `message` text NOT NULL,
              `data` text,
              `ip` varchar(255) NOT NULL,
              `created_time` int(10) unsigned NOT NULL,
              `level` char(10) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统日志';

            -- ----------------------------
            --  Table structure for `region`
            -- ----------------------------
            DROP TABLE IF EXISTS `region`;
            CREATE TABLE `region` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `name` varchar(255) NOT NULL COMMENT '名称',
              `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级id',
              `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序值',
              `depth` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '层级',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='区域表';

            -- ----------------------------
            --  Table structure for `resource`
            -- ----------------------------
            DROP TABLE IF EXISTS `resource`;
            CREATE TABLE `resource` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `name` varchar(255) NOT NULL COMMENT '资源名称',
              `type` varchar(64) NOT NULL COMMENT '资源类型（执行人员:worker）',
              `model` varchar(64) NOT NULL COMMENT '资源型号（如厨师、服务员）',
              `unit` varchar(64) NOT NULL COMMENT '单位',
              `price` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '价格',
              `status` varchar(64) NOT NULL COMMENT '状态（free/busy）',
              `owner_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户id',
              `about` text COMMENT '简介',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='可调配的通用资源表';

            -- ----------------------------
            --  Table structure for `role`
            -- ----------------------------
            DROP TABLE IF EXISTS `role`;
            CREATE TABLE `role` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `name` varchar(255) NOT NULL COMMENT '角色名称',
              `code` varchar(64) NOT NULL COMMENT '角色编码',
              `access_rules` text COMMENT '访问规则集合',
              `owner_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户id',
              `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统内置',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='角色权限表';

            -- ----------------------------
            --  Table structure for `setting`
            -- ----------------------------
            DROP TABLE IF EXISTS `setting`;
            CREATE TABLE `setting` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL DEFAULT '',
              `value` longblob,
              `owner_id` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE KEY `name` (`name`,`owner_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

            -- ----------------------------
            --  Table structure for `stock`
            -- ----------------------------
            DROP TABLE IF EXISTS `stock`;
            CREATE TABLE `stock` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
              `name` varchar(255) NOT NULL COMMENT '商品名称',
              `group_code` varchar(64) NOT NULL DEFAULT 'product' COMMENT '商品组别',
              `category_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类id',
              `provider_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '供应商id',
              `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品当前库存数据',
              `min_amount` int(10) NOT NULL DEFAULT '0' COMMENT '库存下限预警',
              `is_warning` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否预警',
              `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序值',
              `owner_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户id',
              `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='库存表';

            -- ----------------------------
            --  Table structure for `stock_record`
            -- ----------------------------
            DROP TABLE IF EXISTS `stock_record`;
            CREATE TABLE `stock_record` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
              `type` varchar(64) NOT NULL COMMENT '入库:in，出库:out，盘点:check，报损:loss，报溢:gain',
              `old_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作前库存',
              `new_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作后库存',
              `wave_amount` int(10) NOT NULL DEFAULT '0' COMMENT '操作的库存量',
              `cost_price` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '本批(入库)价格',
              `remark` varchar(1024) NOT NULL COMMENT '备注',
              `owner_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户id',
              `operator_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作者用户id',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='库存记录表（出入库）';

            -- ----------------------------
            --  Table structure for `stock_todo`
            -- ----------------------------
            DROP TABLE IF EXISTS `stock_todo`;
            CREATE TABLE `stock_todo` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `target_type` varchar(64) NOT NULL COMMENT '类型可选:goods/goods_set',
              `target_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'goods时就是goods_id，goods_set时就是goods_set_id',
              `creator_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当前用户id',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='待处理列表';

            -- ----------------------------
            --  Table structure for `user`
            -- ----------------------------
            DROP TABLE IF EXISTS `user`;
            CREATE TABLE `user` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `nickname` varchar(255) NOT NULL COMMENT '昵称',
              `type` varchar(64) NOT NULL COMMENT '用户类型（供应商:provider，加盟商:alias）',
              `mobile` varchar(255) NOT NULL COMMENT '手机号码',
              `email` varchar(255) NOT NULL COMMENT '邮箱',
              `password` varchar(64) NOT NULL COMMENT '用户密码',
              `salt` varchar(32) NOT NULL COMMENT '密码SALT',
              `small_avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '小头像',
              `medium_avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '中头像',
              `large_avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '大头像',
              `region_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属区域id',
              `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属主账号id',
              `about` text COMMENT '简介',
              `company_name` varchar(255) NOT NULL DEFAULT '' COMMENT '企业名称',
              `company_address` varchar(255) NOT NULL DEFAULT '' COMMENT '企业地址',
              `roles` varchar(255) NOT NULL COMMENT '用户角色',
              `locked` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否被禁止',
              `new_notification_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未读消息数',
              `created_ip` varchar(64) NOT NULL DEFAULT '' COMMENT '注册IP',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
              `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              `login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
              `login_ip` varchar(64) NOT NULL DEFAULT '' COMMENT '最后登录IP',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户表';
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
