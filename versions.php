<?php

return [
    '4.0.0' => function () {
        $installSql = file_get_contents(__DIR__ . '/forms/install/install.sql');
        sql_execute($installSql, true, false);
    },

    '4.0.1' => function () {
    },

    '4.0.2' => function () {
    },

    '4.0.3' => function () {
    },

    '4.0.4' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_delivery` ADD COLUMN `is_goods`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否商品信息' AFTER `deleted_at`;
ALTER TABLE `zjhj_bd_mch` ALTER column `user_id` SET DEFAULT '0';
ALTER TABLE `zjhj_bd_lottery` ADD COLUMN `buy_goods_id` int(11) NOT NULL COMMENT '购买商品id' AFTER `code_num`;
ALTER TABLE `zjhj_bd_bargain_banner` ADD COLUMN `deleted_at` timestamp NOT NULL AFTER `created_at`;
EOF;
        sql_execute($sql);
    },

    '4.0.5' => function () {
    },

    '4.0.7' => function () {
    },

    '4.0.8' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_banner`
ADD COLUMN `open_type` varchar(65) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '打开方式' AFTER `page_url`,
ADD COLUMN `params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '导航参数' AFTER `open_type`;
alter table `zjhj_bd_core_action_log` modify column `before_update` LONGTEXT;
alter table `zjhj_bd_core_action_log` modify column `after_update` LONGTEXT;

ALTER TABLE `zjhj_bd_user` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE `zjhj_bd_user_info` ADD INDEX `user_id`(`user_id`);
ALTER TABLE `zjhj_bd_option` ADD INDEX `name`(`name`);
ALTER TABLE `zjhj_bd_option` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE `zjhj_bd_order_detail` ADD INDEX `order_id`(`order_id`);
ALTER TABLE `zjhj_bd_order` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE `zjhj_bd_order` ADD INDEX `user_id`(`user_id`);
ALTER TABLE `zjhj_bd_user_card` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE `zjhj_bd_user_card` ADD INDEX `user_id`(`user_id`);
ALTER TABLE `zjhj_bd_goods` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE `zjhj_bd_goods` ADD INDEX `goods_warehouse_id`(`goods_warehouse_id`);
ALTER TABLE `zjhj_bd_goods` ADD INDEX `sign`(`sign`);
ALTER TABLE `zjhj_bd_goods_member_price` ADD INDEX `goods_attr_id`(`goods_attr_id`);
ALTER TABLE `zjhj_bd_goods_share` ADD INDEX `goods_attr_id`(`goods_attr_id`);
ALTER TABLE `zjhj_bd_goods_attr` ADD INDEX `goods_id`(`goods_id`);
ALTER TABLE `zjhj_bd_goods_share` ADD INDEX `goods_id`(`goods_id`);
ALTER TABLE `zjhj_bd_goods_cat_relation` ADD INDEX `goods_warehouse_id`(`goods_warehouse_id`);
EOF;
        sql_execute($sql);
    },

    '4.0.9' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_admin_info` ADD COLUMN `is_default`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否使用默认权限';
alter table `zjhj_bd_core_action_log` add `remark` varchar(255) not null default '';
EOF;
        sql_execute($sql);
    },

    '4.0.10' => function () {
    },

    '4.0.11' => function () {
    },

    '4.0.12' => function () {
        $sql = <<<EOF
alter table `zjhj_bd_mall_members` add `bg_pic_url` varchar(255) not null;
EOF;
        sql_execute($sql);
    },
    '4.0.13' => function () {
        $sql = <<<EOF
alter table `zjhj_bd_pintuan_order_relation` add `robot_id` int(11) not null default 0;
CREATE TABLE `zjhj_bd_pintuan_robots` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `nickname` varchar(65) NOT NULL DEFAULT '' COMMENT '机器人昵称', `avatar` varchar(255) NOT NULL DEFAULT '', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;
EOF;
        sql_execute($sql);
    },

    '4.0.14' => function () {
    },

    '4.0.15' => function () {
        $sql = <<<EOF
CREATE TABLE `zjhj_bd_diy_form` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `form_data` longtext NOT NULL, `created_at` datetime NOT NULL, `is_delete` tinyint(1) NOT NULL, `updated_at` datetime NOT NULL, `deleted_at` datetime NOT NULL, PRIMARY KEY (`id`), KEY `user_id` (`user_id`) USING BTREE, KEY `mall_id` (`mall_id`) USING BTREE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='diy表单信息';
EOF;
        sql_execute($sql);
    },

    '4.0.16' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_booking_setting` ADD COLUMN `goods_poster` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '自定义海报' AFTER `payment_type`;
ALTER TABLE `zjhj_bd_integral_mall_setting` ADD COLUMN `goods_poster` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '自定义海报' AFTER `send_type`;
ALTER TABLE `zjhj_bd_lottery_setting` ADD COLUMN `goods_poster` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '自定义海报' AFTER `send_type`;
ALTER TABLE `zjhj_bd_miaosha_setting` ADD COLUMN `goods_poster` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '自定义海报' AFTER `send_type`;
ALTER TABLE `zjhj_bd_pintuan_setting` ADD COLUMN `goods_poster` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '自定义海报' AFTER `send_type`;
ALTER TABLE `zjhj_bd_step_setting` ADD COLUMN `goods_poster` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '自定义海报' AFTER `is_territorial_limitation`, ADD COLUMN `step_poster` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '步数海报' AFTER `goods_poster`;
EOF;
        sql_execute($sql);
    },

    '4.0.17' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_goods_warehouse` MODIFY COLUMN `detail` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商品详情，图文';
EOF;
        sql_execute($sql);
    },

    '4.0.18' => function () {
    },

    '4.0.19' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_lottery_setting` ADD COLUMN `is_sms` tinyint(1) NOT NULL DEFAULT 0 COMMENT '开启短信提醒' AFTER `goods_poster`, ADD COLUMN `is_mail` tinyint(1) NOT NULL DEFAULT 0 COMMENT '开启邮件提醒' AFTER `is_sms`, ADD COLUMN `is_print` tinyint(1) NOT NULL DEFAULT 0 COMMENT '开启打印' AFTER `is_mail`;
EOF;
        sql_execute($sql);
    },

    '4.0.20' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_attachment_storage` ADD COLUMN `user_id` int NOT NULL DEFAULT 1 COMMENT '存储设置所属账号';
ALTER TABLE `zjhj_bd_admin_info` ADD COLUMN `secondary_permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '二级权限';
CREATE TABLE IF NOT EXISTS `zjhj_bd_bonus_captain` ( `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT , `mall_id` int(11) NOT NULL , `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '队长姓名' , `mobile` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '队长手机' , `user_id` int(11) NOT NULL , `all_bonus` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '累计分红' , `total_bonus` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '已分红' , `expect_bonus` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '预计分红，未到账分红' , `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' , `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '描述' , `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '-1重新申请未提交 0--申请中 1--成功 2--失败 3--处理中' , `all_member` int(11) NOT NULL DEFAULT 0 COMMENT '团员数量' , `created_at` timestamp NOT NULL , `updated_at` timestamp NOT NULL , `deleted_at` timestamp NOT NULL , `apply_at` timestamp NULL DEFAULT NULL , `is_delete` tinyint(1) NOT NULL DEFAULT 0 , PRIMARY KEY (`id`), INDEX `user_id` (`user_id`) USING BTREE ) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='团队分红队长表' AUTO_INCREMENT=1;
CREATE TABLE IF NOT EXISTS `zjhj_bd_bonus_captain_log` ( `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT , `mall_id` int(11) NOT NULL , `handler` int(11) NOT NULL DEFAULT 0 COMMENT '操作人' , `user_id` int(11) NOT NULL COMMENT '队长' , `event` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '事件名' , `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '记录信息' , `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP , `is_delete` tinyint(1) NOT NULL DEFAULT 0 , PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='队长操作日志表' AUTO_INCREMENT=1;
CREATE TABLE IF NOT EXISTS `zjhj_bd_bonus_captain_relation` ( `id` int(11) NOT NULL AUTO_INCREMENT , `captain_id` int(11) NOT NULL COMMENT '队长id' , `user_id` int(11) NOT NULL COMMENT '团队id' , `is_delete` tinyint(1) NOT NULL DEFAULT 0 , `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP , `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' , `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' , PRIMARY KEY (`id`), INDEX `user_id` (`user_id`) USING BTREE ) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=1;
CREATE TABLE IF NOT EXISTS `zjhj_bd_bonus_cash` ( `id` int(11) NOT NULL AUTO_INCREMENT , `mall_id` int(11) NOT NULL , `user_id` int(11) NOT NULL , `order_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '订单号' , `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '提现金额' , `service_charge` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '提现手续费（%）' , `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '提现方式 auto--自动打款 wechat--微信打款 alipay--支付宝打款 bank--银行转账 balance--打款到余额' , `extra` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '额外信息 例如微信账号、支付宝账号等' , `status` int(11) NOT NULL DEFAULT 0 COMMENT '提现状态 0--申请 1--同意 2--已打款 3--驳回' , `is_delete` int(11) NOT NULL DEFAULT 0 , `created_at` datetime NOT NULL , `updated_at` datetime NOT NULL , `deleted_at` datetime NOT NULL , `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL , PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='提现记录表' AUTO_INCREMENT=1;
CREATE TABLE IF NOT EXISTS `zjhj_bd_bonus_cash_log` ( `id` int(11) NOT NULL AUTO_INCREMENT , `mall_id` int(11) NOT NULL , `user_id` int(11) NOT NULL , `type` int(11) NOT NULL DEFAULT 1 COMMENT '类型 1--收入 2--支出' , `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '变动佣金' , `desc` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL , `custom_desc` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL , `is_delete` int(11) NOT NULL DEFAULT 0 , `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' , `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' , `deleted_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' , PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=1;
CREATE TABLE IF NOT EXISTS `zjhj_bd_bonus_order_log` ( `id` int(11) NOT NULL AUTO_INCREMENT , `mall_id` int(11) NOT NULL DEFAULT 0 , `order_id` int(11) NOT NULL DEFAULT 0 COMMENT '订单ID' , `from_user_id` int(11) NOT NULL DEFAULT 0 COMMENT '下单用户ID' , `to_user_id` int(11) NOT NULL DEFAULT 0 COMMENT '受益用户ID' , `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '订单商品实付金额' , `bonus_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '分红金额' , `fail_bonus_price` decimal(10,2) NULL DEFAULT 0.00 COMMENT '失败分红金额' , `status` tinyint(2) NOT NULL DEFAULT 0 COMMENT '0预计分红，1完成分红，2分红失败' , `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' , `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' , `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' , `is_delete` tinyint(2) NOT NULL DEFAULT 0 , `remark` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注' , `bonus_rate` tinyint(4) NOT NULL DEFAULT 0 COMMENT '下单时的分红比例%' , PRIMARY KEY (`id`), UNIQUE INDEX `order_id` (`order_id`) USING BTREE , INDEX `from_user_id` (`from_user_id`) USING BTREE , INDEX `to_user_id` (`to_user_id`) USING BTREE ) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=1;
CREATE TABLE IF NOT EXISTS `zjhj_bd_bonus_setting` ( `id` int(11) NOT NULL AUTO_INCREMENT , `mall_id` int(11) NOT NULL , `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL , `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL , `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间' , `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间' , `is_delete` int(11) NOT NULL DEFAULT 0 COMMENT '是否删除 0--未删除 1--已删除' , `deleted_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '删除时间' , PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='团队分红设置' AUTO_INCREMENT=1;
EOF;
        sql_execute($sql);
    },

    '4.0.21' => function () {
        $sql = <<<EOF
alter table `zjhj_bd_mch_setting` add `is_web_service` tinyint(1) NOT NULL default 0;
alter table `zjhj_bd_mch_setting` add `web_service_url` varchar(255) NOT NULL default '';
alter table `zjhj_bd_mch_setting` add `web_service_pic` varchar(255) NOT NULL default '';
EOF;
        sql_execute($sql);
    },

    '4.0.22' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_user_card` MODIFY COLUMN `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `data`, MODIFY COLUMN `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `start_time`;
EOF;
        sql_execute($sql);
    },

    '4.0.24' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_order` ADD COLUMN `customer_name` VARCHAR(65) DEFAULT '' NULL COMMENT '京东商家编号' AFTER `send_time`;
ALTER TABLE `zjhj_bd_order_refund` ADD COLUMN `customer_name` VARCHAR(65) DEFAULT '' NULL COMMENT '京东商家编号' AFTER `send_time`, ADD COLUMN `merchant_customer_name` VARCHAR(65) DEFAULT '' NULL COMMENT '商家京东商家编号' AFTER `confirm_time`;
EOF;
        sql_execute($sql);
    },

    '4.0.25' => function () {
    },

    '4.0.26' => function () {
    },

    '4.0.27' => function () {
    },

    '4.0.28' => function () {
    },

    '4.0.30' => function () {
        $sql = <<<EOF
alter table zjhj_bd_mall_member_orders change detail detail MEDIUMTEXT;
CREATE TABLE `zjhj_bd_scan_code_pay_activities` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `name` varchar(255) NOT NULL DEFAULT '' COMMENT '活动名称', `start_time` timestamp NOT NULL COMMENT '活动开始时间', `end_time` timestamp NOT NULL COMMENT '活动结束时间', `send_type` tinyint(1) NOT NULL DEFAULT '2' COMMENT '1.赠送所有规则|2.赠送满足最高规则', `rules` text COMMENT '买单规则', `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_scan_code_pay_activities_groups` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL DEFAULT '', `activity_id` int(11) NOT NULL, `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_scan_code_pay_activities_groups_level` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `group_id` int(11) NOT NULL, `level` int(11) NOT NULL, `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_scan_code_pay_activities_groups_rules` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `group_id` int(11) NOT NULL, `rules_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1.赠送规则|2.优惠规则', `consume_money` decimal(10,2) NOT NULL COMMENT '单次消费金额', `send_integral_num` int(11) NOT NULL COMMENT '赠送积分', `send_integral_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1.固定值|2.百分比', `send_money` decimal(10,2) NOT NULL COMMENT '赠送余额', `preferential_money` decimal(10,2) NOT NULL COMMENT '优惠金额', `integral_deduction` int(11) NOT NULL COMMENT '积分抵扣', `integral_deduction_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1.固定值|2.百分比', `is_coupon` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可使用优惠券', `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_scan_code_pay_activities_groups_rules_cards` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `group_rule_id` int(11) NOT NULL, `card_id` int(11) NOT NULL, `send_num` int(11) NOT NULL COMMENT '赠送数量', `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_scan_code_pay_activities_groups_rules_coupons` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `group_rule_id` int(11) NOT NULL, `coupon_id` int(11) NOT NULL, `send_num` int(11) NOT NULL COMMENT '赠送数量', `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_scan_code_pay_orders` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `order_id` int(11) NOT NULL, `activity_preferential_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '活动优惠价格', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_scan_code_pay_setting` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `is_scan_code_pay` tinyint(1) NOT NULL DEFAULT '0', `payment_type` text NOT NULL, `is_share` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启分销', `is_sms` tinyint(1) NOT NULL DEFAULT '0', `is_mail` tinyint(1) NOT NULL DEFAULT '0', `share_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1.百分比|2.固定金额', `share_commission_first` decimal(10,2) NOT NULL DEFAULT '0.00', `share_commission_second` decimal(10,2) NOT NULL DEFAULT '0.00', `share_commission_third` decimal(10,2) NOT NULL DEFAULT '0.00', `poster` mediumtext NOT NULL COMMENT '自定义海报', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `zjhj_bd_bonus_captain` ADD COLUMN `level` int(11) NOT NULL DEFAULT 0 COMMENT '会员等级:0. 普通成员 关联等级表' AFTER `remark`;
CREATE TABLE `zjhj_bd_bonus_members` ( `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT , `mall_id` int(11) NOT NULL , `level` int(11) UNSIGNED NOT NULL COMMENT '等级' , `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '等级名称' , `auto_update` tinyint(1) NOT NULL COMMENT '是否开启自动升级' , `update_type` int(11) NOT NULL DEFAULT 0 COMMENT '升级条件类型' , `update_condition` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '升级条件' , `rate` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '分红比例' , `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态 0--禁用 1--启用' , `created_at` timestamp NOT NULL , `updated_at` timestamp NOT NULL , `deleted_at` timestamp NOT NULL , `is_delete` tinyint(1) NOT NULL DEFAULT 0 , PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=1 ROW_FORMAT=DYNAMIC ;
EOF;
        sql_execute($sql);
    },

    '4.0.32' => function () {
    },

    '4.0.34' => function () {
        $sql = <<<EOF
alter table `zjhj_bd_goods_cats` add `is_show` tinyint(1) default '1';
EOF;
        sql_execute($sql);
    },

    '4.0.35' => function () {
    },

    '4.0.36' => function () {
    },

    '4.0.37' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_user_info` ADD INDEX `parent_id`(`parent_id`);
ALTER TABLE `zjhj_bd_booking_setting` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE `zjhj_bd_share` ADD INDEX `mall_id`(`mall_id`), ADD INDEX `is_delete`(`is_delete`);

ALTER TABLE `zjhj_bd_lottery_setting`
ADD COLUMN `cs_status`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否开启客服提示' AFTER `is_print`,
ADD COLUMN `cs_prompt_pic`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '客服提示图片' AFTER `cs_status`,
ADD COLUMN `cs_wechat`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '客服微信号' AFTER `cs_prompt_pic`,
ADD COLUMN `cs_wechat_flock_qrcode_pic`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '微信群' AFTER `cs_wechat`;

alter table zjhj_bd_printer_setting add store_id int(11) default '0' not null;
alter table zjhj_bd_pintuan_goods_groups add group_num int(11) default '0' not null;
alter table zjhj_bd_mall add expired_at TIMESTAMP default '0000-00-00 00:00:00' not null;
EOF;
        sql_execute($sql);
    },

    '4.0.38' => function () {
    },

    '4.0.39' => function () {
    },

    '4.0.40' => function () {
    },

    '4.1.0' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_integral_mall_setting` MODIFY COLUMN `send_type` longtext NOT NULL COMMENT '发货方式';
ALTER TABLE `zjhj_bd_lottery_setting` MODIFY COLUMN `send_type` longtext NOT NULL COMMENT '发货方式';
ALTER TABLE `zjhj_bd_miaosha_setting` MODIFY COLUMN `send_type` longtext NOT NULL;
ALTER TABLE `zjhj_bd_pintuan_setting` MODIFY COLUMN `send_type` longtext NOT NULL COMMENT '发货方式';
ALTER TABLE `zjhj_bd_pond_setting` MODIFY COLUMN `send_type` longtext NOT NULL COMMENT '发货方式';
ALTER TABLE `zjhj_bd_scratch_setting` MODIFY COLUMN `send_type` longtext NOT NULL COMMENT '发货方式';
ALTER TABLE `zjhj_bd_step_setting` MODIFY COLUMN `send_type` longtext NOT NULL COMMENT '发货方式';
ALTER TABLE `zjhj_bd_mch_setting` MODIFY COLUMN `send_type` longtext NOT NULL COMMENT '发货方式';
ALTER TABLE `zjhj_bd_order` ADD COLUMN `distance` INT DEFAULT -1 NULL COMMENT '同城配送距离，-1不在范围内，正数为距离KM' AFTER `auto_sales_time`, ADD COLUMN `city_mobile` VARCHAR(100) DEFAULT '' NULL COMMENT '同城配送联系方式' AFTER `distance`;
CREATE TABLE `zjhj_bd_city_delivery_setting` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT '0', `key` varchar(60) DEFAULT NULL, `value` text, `created_at` timestamp NULL DEFAULT NULL, `updated_at` timestamp NULL DEFAULT NULL, `deleted_at` timestamp NULL DEFAULT NULL, `is_delete` tinyint(2) DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `zjhj_bd_aliapp_config`;
CREATE TABLE `zjhj_bd_aliapp_config` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `appid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, `app_private_key` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, `alipay_public_key` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, `cs_tnt_inst_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '', `cs_scene` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '', `app_aes_secret` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '内容加密AES密钥', `transfer_app_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '打款到用户app_id', `transfer_app_private_key` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '打款到用户app_private_key', `transfer_alipay_public_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL, `transfer_appcert` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '应用公钥证书', `transfer_alipay_rootcert` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '支付宝根证书', `created_at` timestamp(0) NOT NULL, `updated_at` timestamp(0) NOT NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;
CREATE TABLE `zjhj_bd_aliapp_template` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `tpl_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, `tpl_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '', `created_at` timestamp(0) NULL DEFAULT NULL, `updated_at` timestamp(0) NULL DEFAULT NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;
CREATE TABLE `zjhj_bd_bdapp_config` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `created_at` timestamp(0) NULL DEFAULT NULL, `updated_at` timestamp(0) NULL DEFAULT NULL, `app_id` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, `app_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, `app_secret` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, `pay_dealid` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, `pay_public_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL, `pay_private_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL, `pay_app_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;
CREATE TABLE `zjhj_bd_bdapp_order` ( `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, `order_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '订单号', `bd_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '', `bd_order_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '百度平台订单ID', `bd_refund_batch_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '百度平台退款批次号', `bd_refund_money` int(11) NOT NULL DEFAULT 0, `refund_money` decimal(10, 2) NOT NULL DEFAULT 0.00, `is_refund` tinyint(4) NOT NULL DEFAULT 0, `created_at` timestamp(0) NOT NULL, `updated_at` timestamp(0) NOT NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '百度订单号与商城订单号关联表' ROW_FORMAT = Dynamic;
CREATE TABLE `zjhj_bd_bdapp_template` ( `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `tpl_name` varchar(65) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '', `tpl_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '', `created_at` timestamp(0) NOT NULL, `updated_at` timestamp(0) NOT NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;
CREATE TABLE `zjhj_bd_ttapp_config` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `mch_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '商户号', `app_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, `app_secret` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, `pay_app_secret` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, `pay_app_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, `alipay_app_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, `alipay_public_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL, `alipay_private_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL, `created_at` timestamp(0) NULL DEFAULT NULL, `updated_at` timestamp(0) NULL DEFAULT NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;
CREATE TABLE `zjhj_bd_ttapp_template` ( `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `tpl_name` varchar(65) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '', `tpl_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '', `created_at` timestamp(0) NOT NULL, `updated_at` timestamp(0) NOT NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;
EOF;
        sql_execute($sql);
    },

    '4.1.1' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_address` ADD COLUMN `latitude` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '经度' AFTER `deleted_at`, ADD COLUMN `longitude` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '纬度' AFTER `latitude`, ADD COLUMN `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '位置' AFTER `longitude`;
EOF;
        sql_execute($sql);
    },

    '4.1.2' => function () {
    },

    '4.1.3' => function () {
    },

    '4.1.4' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_bonus_order_log` CHANGE `bonus_rate` `bonus_rate` VARCHAR(32) DEFAULT '0'  NOT NULL   COMMENT '下单时的分红比例%';
EOF;
        sql_execute($sql);
    },

    '4.1.5' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_order` ADD COLUMN `location` varchar(255) NULL, ADD COLUMN `city_name` varchar(255) NULL, ADD COLUMN `city_info` varchar(255) NULL;
ALTER TABLE `zjhj_bd_order` CHANGE COLUMN `is_offline` `send_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '配送方式：0--快递配送 1--到店自提 2--同城配送';
CREATE TABLE `zjhj_bd_city_deliveryman` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT '0', `mch_id` int(11) NOT NULL DEFAULT '0', `name` varchar(255) NOT NULL COMMENT '配送员名称', `mobile` varchar(255) NOT NULL COMMENT '联系方式', `is_delete` tinyint(1) NOT NULL, `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
EOF;
        sql_execute($sql);
    },

    '4.1.6' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_topic` ADD COLUMN `pic_list`  longtext NULL, ADD COLUMN `detail`  longtext NULL, ADD COLUMN `abstract`  varchar(255) NOT NULL DEFAULT '' COMMENT '摘要';
EOF;
        sql_execute($sql);
    },

    '4.1.7' => function () {
        $sql = <<<EOF
alter table `zjhj_bd_pintuan_order_relation` add cancel_status tinyint(1) not NULL default '0' COMMENT '拼团订单取消状态:0.未取消|1.超出拼团总人数取消';
EOF;
        sql_execute($sql);
    },

    '4.1.8' => function () {
        $sql = <<<EOF
CREATE TABLE `zjhj_bd_advance_banner` ( `id` int(11) NOT NULL AUTO_INCREMENT, `banner_id` int(11) NOT NULL, `mall_id` int(11) NOT NULL, `is_delete` tinyint(1) NOT NULL, `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP, `deleted_at` timestamp NOT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='预售轮播图';
CREATE TABLE `zjhj_bd_advance_goods` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `goods_id` int(11) NOT NULL, `mall_id` int(11) NOT NULL, `ladder_rules` varchar(4096) NOT NULL DEFAULT '' COMMENT '阶梯规则', `deposit` decimal(10,2) NOT NULL DEFAULT '0.00', `swell_deposit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '定金膨胀金', `start_prepayment_at` timestamp NOT NULL COMMENT '预售开始时间', `end_prepayment_at` timestamp NOT NULL COMMENT '预售结束时间', `pay_limit` int(11) NOT NULL COMMENT '尾款支付时间 -1:无限制', `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
CREATE TABLE `zjhj_bd_advance_goods_attr` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `deposit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品所需定金', `swell_deposit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '定金膨胀金', `goods_id` int(11) NOT NULL, `goods_attr_id` int(11) NOT NULL, `is_delete` tinyint(1) NOT NULL DEFAULT '0', `advance_num` int(11) NOT NULL DEFAULT '0' COMMENT '预约数量', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
CREATE TABLE `zjhj_bd_advance_order` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `goods_id` int(11) NOT NULL COMMENT '商品ID', `goods_attr_id` int(11) NOT NULL COMMENT '规格ID', `goods_num` int(11) NOT NULL DEFAULT '0', `order_id` int(11) NOT NULL DEFAULT '0', `order_no` varchar(255) NOT NULL DEFAULT '0', `advance_no` varchar(255) NOT NULL COMMENT '定金订单号', `deposit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '定金', `swell_deposit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '膨胀金', `is_cancel` tinyint(2) NOT NULL DEFAULT '0' COMMENT '1取消', `cancel_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `is_refund` tinyint(2) NOT NULL DEFAULT '0' COMMENT '1退款', `is_delete` tinyint(2) NOT NULL DEFAULT '0' COMMENT '1删除', `is_pay` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否支付：0.未支付|1.已支付', `is_recycle` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否加入回收站 0.否|1.是', `pay_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付方式：1.在线支付 2.货到付款 3.余额支付', `pay_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注', `auto_cancel_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '自动取消时间', `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `goods_info` longtext NOT NULL, `token` varchar(32) NOT NULL, `order_token` varchar(32) DEFAULT NULL, `preferential_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '活动优惠金额', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_advance_order_submit_result` ( `id` int(11) NOT NULL AUTO_INCREMENT, `token` varchar(32) NOT NULL, `data` longtext, PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_advance_setting` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `is_advance` tinyint(1) NOT NULL DEFAULT '1', `payment_type` text NOT NULL, `deposit_payment_type` varchar(255) NOT NULL DEFAULT '', `is_share` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启分销', `is_sms` tinyint(1) NOT NULL DEFAULT '0', `is_mail` tinyint(1) NOT NULL DEFAULT '0', `is_print` tinyint(1) NOT NULL DEFAULT '0', `is_territorial_limitation` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启区域允许购买', `goods_poster` longtext NOT NULL, `send_type` varchar(255) NOT NULL DEFAULT '' COMMENT '发货方式', `over_time` int(11) NOT NULL DEFAULT '0' COMMENT '未支付定金订单超时时间', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
EOF;
        sql_execute($sql);
    },

    '4.1.9' => function () {
    },

    '4.1.10' => function () {
    },

    '4.1.11' => function () {
        $sql = <<<EOF
-- 首页接口索引优化
ALTER TABLE `zjhj_bd_goods_cats` ADD INDEX `index1`(`is_delete`,`status`,`is_show`,`mch_id`,`mall_id`);
ALTER TABLE `zjhj_bd_goods_member_price` ADD INDEX `index1`(`is_delete`,`goods_id`,`level`);
ALTER TABLE `zjhj_bd_miaosha_goods` ADD INDEX `index1`(`is_delete`,`open_date`,`open_time`);
ALTER TABLE `zjhj_bd_mall_goods` ADD INDEX `index1`(`goods_id`);
ALTER TABLE `zjhj_bd_miaosha_goods` ADD INDEX `index2`(`is_delete`,`goods_id`);
ALTER TABLE `zjhj_bd_goods` ADD INDEX `index1`(`mall_id`,`is_delete`,`sign`,`status`,`goods_warehouse_id`);
ALTER TABLE `zjhj_bd_order` ADD INDEX `index1`(`mall_id`,`is_delete`,`is_pay`,`pay_type`,`cancel_status`);
ALTER TABLE `zjhj_bd_goods` ADD INDEX `index2`(`is_delete`,`mall_id`,`status`);

-- 超级会员卡
CREATE TABLE `zjhj_bd_vip_card` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `name` varchar(255) NOT NULL DEFAULT '' COMMENT '会员卡名称', `cover` varchar(2048) NOT NULL DEFAULT '' COMMENT '卡片样式', `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:指定商品类别 1:指定商品 2:全场通用', `type_info` varchar(2048) NOT NULL DEFAULT '', `discount` decimal(11,1) NOT NULL DEFAULT '0.0' COMMENT '折扣', `is_discount` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:关闭 1开启', `is_free_delivery` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:不包邮 1:包邮', `status` tinyint(1) NOT NULL DEFAULT '0', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_vip_card_appoint_goods` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `goods_id` int(11) NOT NULL, `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_vip_card_cards` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `detail_id` int(11) NOT NULL COMMENT 'vip卡id', `card_id` int(11) NOT NULL COMMENT '卡券id', `send_num` int(11) NOT NULL COMMENT '赠送数量', `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
CREATE TABLE `zjhj_bd_vip_card_coupons` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `detail_id` int(11) NOT NULL, `coupon_id` int(11) NOT NULL, `send_num` int(11) NOT NULL COMMENT '赠送数量', `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
CREATE TABLE `zjhj_bd_vip_card_detail` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `vip_id` int(11) NOT NULL, `name` varchar(255) NOT NULL COMMENT '标题', `cover` varchar(2048) NOT NULL DEFAULT '' COMMENT '子卡封面', `expire_day` int(11) NOT NULL, `price` decimal(10,2) NOT NULL, `num` int(11) NOT NULL DEFAULT '0' COMMENT '库存', `sort` int(11) NOT NULL DEFAULT '100' COMMENT '排序', `send_integral_num` int(11) NOT NULL DEFAULT '0' COMMENT '积分赠送', `send_integral_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '积分赠送类型 1.固定值|2.百分比', `send_balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '赠送余额', `title` varchar(255) NOT NULL DEFAULT '' COMMENT '使用说明', `content` varchar(2048) NOT NULL DEFAULT '' COMMENT '使用内容', `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:正常 1：停发', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_vip_card_discount` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `order_id` int(11) NOT NULL, `order_detail_id` int(11) NOT NULL, `main_id` int(11) NOT NULL DEFAULT '0', `main_name` varchar(255) NOT NULL DEFAULT '' COMMENT '主卡名称', `detail_id` int(11) NOT NULL DEFAULT '0', `detail_name` varchar(255) NOT NULL DEFAULT '' COMMENT '子卡名称', `discount_num` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '折扣', `discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '折扣优惠', `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_vip_card_order` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `order_id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `main_id` int(11) NOT NULL COMMENT '主卡id', `main_name` varchar(255) NOT NULL DEFAULT '' COMMENT '主卡名称', `detail_id` int(11) NOT NULL, `detail_name` varchar(255) NOT NULL DEFAULT '' COMMENT '子卡名称', `price` decimal(10,2) NOT NULL COMMENT '购买价格', `expire` int(11) NOT NULL COMMENT '有效期', `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未售 1已售', `all_send` varchar(2048) NOT NULL DEFAULT '', `is_admin_add` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否后台添加', `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, `updated_at` timestamp NULL DEFAULT NULL, `deleted_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
CREATE TABLE `zjhj_bd_vip_card_setting` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `is_vip_card` tinyint(1) NOT NULL DEFAULT '0', `payment_type` text NOT NULL, `is_share` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启分销', `is_sms` tinyint(1) NOT NULL DEFAULT '0', `is_mail` tinyint(1) NOT NULL DEFAULT '0', `is_agreement` tinyint(1) NOT NULL DEFAULT '0', `agreement_title` varchar(255) NOT NULL DEFAULT '', `agreement_content` text NOT NULL, `is_buy_become_share` tinyint(1) NOT NULL DEFAULT '0' COMMENT '购买成为分销商 0:关闭 1开启', `share_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1.百分比|2.固定金额', `share_commission_first` decimal(10,2) NOT NULL DEFAULT '0.00', `share_commission_second` decimal(10,2) NOT NULL DEFAULT '0.00', `share_commission_third` decimal(10,2) NOT NULL DEFAULT '0.00', `form` text NOT NULL, `rules` text NOT NULL COMMENT '允许的插件', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
CREATE TABLE `zjhj_bd_vip_card_user` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `main_id` int(11) NOT NULL DEFAULT '0', `detail_id` int(11) NOT NULL, `image_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:指定商品类别 1:指定商品 2:全场通用', `image_type_info` varchar(2048) NOT NULL DEFAULT '', `image_discount` decimal(11,1) NOT NULL DEFAULT '0.0' COMMENT '折扣', `image_is_free_delivery` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:不包邮 1:包邮', `image_main_name` varchar(255) NOT NULL DEFAULT '' COMMENT '主卡名称', `image_name` varchar(255) NOT NULL COMMENT '名称', `all_send` varchar(2048) NOT NULL DEFAULT '' COMMENT '所有赠送信息', `data` longtext COMMENT '额外信息字段', `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, PRIMARY KEY (`id`), KEY `mall_id` (`mall_id`) USING BTREE, KEY `user_id` (`user_id`) USING BTREE ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
EOF;
        sql_execute($sql);
    },

    '4.1.14' => function () {
    },


    '4.1.16' => function () {
    },

    '4.1.17' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_bargain_order` CHANGE `created_at` `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `bargain_goods_data`;
EOF;
        sql_execute($sql);
    },

    '4.2.0' => function () {
        $sql = <<<EOF
alter table `zjhj_bd_goods` add `confine_order_count` int(11) not NULL default '-1';
alter table `zjhj_bd_cart` add `attr_info` text;
alter table `zjhj_bd_order_refund` add `refund_time` TIMESTAMP not NULL default '0000-00-00 00:00:00';
alter table `zjhj_bd_order_refund` add `is_refund` tinyint(1) not NULL default '2' COMMENT '是否打款，2代表旧数据';
alter table `zjhj_bd_order_detail` add `goods_no` varchar(60) not NULL default '' comment '商品货号';
CREATE TABLE `zjhj_bd_order_detail_express` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `mch_id` int(11) NOT NULL, `order_id` int(11) NOT NULL COMMENT '订单ID', `express` varchar(65) NOT NULL DEFAULT '', `send_type` tinyint(1) NOT NULL COMMENT '1.快递|2.其它方式', `express_no` varchar(255) NOT NULL DEFAULT '', `merchant_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '商家留言', `express_content` varchar(255) NOT NULL DEFAULT '' COMMENT '物流内容', `is_delete` tinyint(4) NOT NULL DEFAULT '0', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_order_detail_express_relation` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `mch_id` int(11) NOT NULL, `order_id` int(11) NOT NULL, `order_detail_id` int(11) NOT NULL, `order_detail_express_id` int(11) NOT NULL, `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_order_comments_templates` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `mch_id` int(11) NOT NULL DEFAULT '0', `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '模板类型:1.好评|2.中评|3.差评', `title` varchar(65) NOT NULL DEFAULT '' COMMENT '标题', `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `zjhj_bd_goods` ADD COLUMN `is_area_limit` TINYINT ( 1 ) NOT NULL DEFAULT 0 COMMENT '是否单独区域购买' AFTER `confine_order_count`, ADD COLUMN `area_limit` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL AFTER `is_area_limit`;
ALTER TABLE `zjhj_bd_lottery_log` ADD INDEX `lottery_id` ( `lottery_id` ) USING BTREE, ADD INDEX `user_id` ( `user_id` ) USING BTREE;
ALTER TABLE `zjhj_bd_attachment_group` ADD COLUMN `is_recycle` TINYINT ( 1 ) NOT NULL DEFAULT 0 COMMENT '是否加入回收站 0.否|1.是' AFTER `deleted_at`, ADD COLUMN `type` TINYINT ( 2 ) NOT NULL DEFAULT 0 COMMENT '0 图片 1商品' AFTER `is_recycle`;
ALTER TABLE `zjhj_bd_attachment` ADD COLUMN `is_recycle` TINYINT ( 1 ) NOT NULL DEFAULT 0 COMMENT '是否加入回收站 0.否|1.是' AFTER `is_delete`;
EOF;
        sql_execute($sql);
    },

    '4.2.1' => function () {
    },

    '4.2.2' => function () {
    },

    '4.2.3' => function () {
        $sql = <<<EOF
ALTER TABLE zjhj_bd_order_refund ADD reality_refund_price DECIMAL ( 10, 2 ) NOT NULL DEFAULT '0' COMMENT '商家实际退款金额';
EOF;
        sql_execute($sql);
    },

    '4.2.4' => function () {
        $sql = <<<EOF
ALTER TABLE zjhj_bd_order_refund ADD reality_refund_price DECIMAL ( 10, 2 ) NOT NULL DEFAULT '0' COMMENT '商家实际退款金额';
EOF;
        sql_execute($sql);
    },

    '4.2.5' => function () {
        $sql = <<<EOF
CREATE TABLE `zjhj_bd_quick_share_goods` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `goods_id` int(11) NOT NULL DEFAULT '0', `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态', `share_text` varchar(255) NOT NULL COMMENT '分享文本', `share_pic` longtext NOT NULL COMMENT '素材图片', `material_sort` int(11) NOT NULL DEFAULT '0' COMMENT '素材排序', `is_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否置顶', `material_video_url` varchar(255) NOT NULL DEFAULT '' COMMENT '动态视频', `material_cover_url` varchar(255) NOT NULL DEFAULT '' COMMENT '视频封面', `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, PRIMARY KEY (`id`), KEY `goods_id` (`goods_id`) USING BTREE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_quick_share_setting` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发圈对象 仅素材 1全部商品', `goods_poster` longtext NOT NULL, `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
EOF;
        sql_execute($sql);
    },

    '4.2.8' => function () {
        $sql = <<<EOF
ALTER TABLE zjhj_bd_order_refund ADD merchant_express_content VARCHAR ( 255 ) NOT NULL DEFAULT '' COMMENT '物流内容';
EOF;
        sql_execute($sql);
    },

    '4.2.9' => function () {
        $sql = <<<EOF
ALTER TABLE zjhj_bd_goods_card_relation ADD num INT ( 11 ) NOT NULL DEFAULT 1 COMMENT '卡券数量';
EOF;
        sql_execute($sql);
    },


    '4.2.10' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_order_detail_express` ADD `customer_name` VARCHAR ( 255 ) NOT NULL DEFAULT '' COMMENT '京东物流编号';

CREATE TABLE `zjhj_bd_gift_log` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT '0', `user_id` int(11) NOT NULL DEFAULT '0', `num` int(11) NOT NULL DEFAULT '0' COMMENT '礼物总数', `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `is_delete` tinyint(1) NOT NULL DEFAULT '0', `is_confirm` tinyint(1) NOT NULL DEFAULT '0' COMMENT '送礼状态：0.未完成送礼|1.已完成送礼', `type` varchar(60) NOT NULL COMMENT '送礼方式', `open_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开奖时间', `open_num` int(11) NOT NULL DEFAULT '0' COMMENT '开奖所需人数', `open_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0一人拿奖，1多人各领一份奖', `bless_word` varchar(200) NOT NULL COMMENT '祝福语', `auto_refund_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '自动退款时间', `is_pay` tinyint(1) NOT NULL DEFAULT '0', `order_id` int(11) NOT NULL DEFAULT '0', `is_cancel` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE `zjhj_bd_gift_lottery` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT '0', `send_order_id` int(11) NOT NULL DEFAULT '0', `user_id` int(11) NOT NULL DEFAULT '0', `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `is_delete` tinyint(1) NOT NULL DEFAULT '0', `is_prize` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未中，1中奖', PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `zjhj_bd_gift_open_result` ( `id` int(11) NOT NULL AUTO_INCREMENT, `token` varchar(32) NOT NULL, `data` longtext, PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE `zjhj_bd_gift_order` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT '0', `order_no` varchar(255) NOT NULL DEFAULT '', `goods_id` int(11) NOT NULL DEFAULT '0', `goods_attr_id` int(11) NOT NULL DEFAULT '0', `num` int(11) NOT NULL DEFAULT '0', `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '商城订单ID', `type` varchar(60) NOT NULL COMMENT '送礼方式', `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `is_delete` tinyint(1) NOT NULL DEFAULT '0', `user_order_id` int(11) NOT NULL DEFAULT '0', `is_refund` tinyint(1) NOT NULL DEFAULT '0' COMMENT '退款，前端显示超时', `buy_order_detail_id` int(11) NOT NULL DEFAULT '0' COMMENT '买礼物的商城订单详情id', `gift_id` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE `zjhj_bd_gift_order_submit_result` ( `id` int(11) NOT NULL AUTO_INCREMENT, `token` varchar(32) NOT NULL, `data` longtext, PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE `zjhj_bd_gift_send_order` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT '0', `mch_id` int(11) NOT NULL DEFAULT '0', `user_id` int(11) NOT NULL DEFAULT '0', `gift_id` int(11) NOT NULL DEFAULT '0' COMMENT 'gift_log的id', `order_no` varchar(60) NOT NULL DEFAULT '', `total_price` decimal(10,2) NOT NULL COMMENT '订单总金额(含运费)', `total_pay_price` decimal(10,2) NOT NULL COMMENT '实际支付总费用(含运费）', `is_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支付：0.未支付|1.已支付', `pay_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '支付方式：1.在线支付 2.货到付款 3.余额支付', `pay_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '支付时间', `is_refund` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未退款，1已退款', `is_confirm` tinyint(1) NOT NULL DEFAULT '0' COMMENT '送礼状态：0.未完成送礼|1.已完成送礼', `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `is_delete` tinyint(1) NOT NULL DEFAULT '0', `support_pay_types` text NOT NULL COMMENT '支持的支付方式，空表示支持系统设置支持的所有方式', `token` varchar(32) NOT NULL, `total_goods_price` decimal(10,2) NOT NULL DEFAULT '0.00', `total_goods_original_price` decimal(10,2) NOT NULL DEFAULT '0.00', `member_discount_price` decimal(10,2) NOT NULL DEFAULT '0.00', `use_user_coupon_id` int(11) NOT NULL DEFAULT '0', `coupon_discount_price` decimal(10,2) NOT NULL DEFAULT '0.00', `use_integral_num` int(11) NOT NULL DEFAULT '0', `integral_deduction_price` decimal(10,2) NOT NULL DEFAULT '0.00', `is_cancel` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE `zjhj_bd_gift_send_order_detail` ( `id` int(11) NOT NULL AUTO_INCREMENT, `send_order_id` int(11) NOT NULL, `goods_id` int(11) NOT NULL, `goods_attr_id` int(11) NOT NULL DEFAULT '0', `goods_info` longtext COMMENT '购买商品信息', `num` int(11) NOT NULL, `unit_price` decimal(10,2) NOT NULL COMMENT '商品单价', `total_original_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品原总价(优惠前)', `total_price` decimal(10,2) NOT NULL COMMENT '商品总价(优惠后)', `member_discount_price` decimal(10,2) NOT NULL DEFAULT '0.00', `is_refund` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未退款，1已退款', `refund_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '售后状态 0--未售后 1--售后中 2--售后结束', `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `is_delete` tinyint(1) NOT NULL DEFAULT '0', `receive_num` int(11) NOT NULL DEFAULT '0' COMMENT '已领取数量', `refund_price` decimal(10,2) NOT NULL DEFAULT '0.00', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE `zjhj_bd_gift_setting` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT '0', `title` varchar(200) NOT NULL, `type` varchar(200) NOT NULL DEFAULT '[]' COMMENT '玩法', `auto_refund` int(11) NOT NULL DEFAULT '0' COMMENT '自动退款天数', `auto_remind` int(11) NOT NULL DEFAULT '0' COMMENT '送礼失败提醒天数', `bless_word` varchar(200) NOT NULL COMMENT '祝福语', `ask_gift` varchar(200) NOT NULL COMMENT '求礼物', `is_share` tinyint(1) NOT NULL DEFAULT '0', `is_sms` tinyint(1) NOT NULL DEFAULT '0', `is_mail` tinyint(1) NOT NULL DEFAULT '0', `is_print` tinyint(1) NOT NULL DEFAULT '0', `payment_type` text NOT NULL, `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `is_delete` tinyint(1) NOT NULL DEFAULT '0', `poster` longtext NOT NULL COMMENT '海报', `background` varchar(200) NOT NULL DEFAULT '[]' COMMENT '自定义背景', `theme` text NOT NULL COMMENT '主题色', `send_type` varchar(200) NOT NULL DEFAULT '[]', `explain` text NOT NULL COMMENT '规则说明', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE `zjhj_bd_gift_user_order` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT '0', `user_id` int(11) NOT NULL DEFAULT '0', `gift_id` int(11) NOT NULL DEFAULT '0', `is_turn` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否转赠0未转1已转', `turn_no` varchar(255) NOT NULL DEFAULT '' COMMENT '转赠码', `turn_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '被转赠用户ID', `is_receive` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未领取，1已领取', `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `is_delete` tinyint(1) NOT NULL DEFAULT '0', `is_win` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未中，1已中', `token` varchar(32) NOT NULL DEFAULT '', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
EOF;
        sql_execute($sql);
    },

    '4.2.11' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_booking_setting`
MODIFY COLUMN `created_at`  timestamp NOT NULL AFTER `form_data`,
MODIFY COLUMN `updated_at`  timestamp NOT NULL AFTER `created_at`;

ALTER TABLE `zjhj_bd_pond_log_coupon_relation`
MODIFY COLUMN `created_at`  timestamp NOT NULL AFTER `is_delete`,
MODIFY COLUMN `deleted_at`  timestamp NOT NULL AFTER `created_at`;


ALTER TABLE `zjhj_bd_pond_order`
MODIFY COLUMN `created_at`  timestamp NOT NULL AFTER `order_id`;

ALTER TABLE `zjhj_bd_scratch_log`
MODIFY COLUMN `deleted_at`  timestamp NOT NULL AFTER `is_delete`;

ALTER TABLE `zjhj_bd_address`
MODIFY COLUMN `created_at`  timestamp NOT NULL AFTER `is_delete`;
EOF;
        sql_execute($sql);
    },

    '4.2.12' => function () {
    },

    '4.2.13' => function () {
        $sql = <<<EOF
CREATE TABLE `zjhj_bd_footprint_data_log` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `key` varchar(60) NOT NULL, `value` varchar(60) NOT NULL, `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00', `is_delete` tinyint(1) NOT NULL DEFAULT '0', `statistics_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '上一次统计的时间', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_footprint_goods_log` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT '0', `user_id` int(11) NOT NULL DEFAULT '0', `goods_id` int(11) NOT NULL DEFAULT '0', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, `is_delete` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

ALTER TABLE  `zjhj_bd_formid` ADD INDEX `user_id`(`user_id`);
ALTER TABLE  `zjhj_bd_formid` ADD INDEX `created_at`(`created_at`);
ALTER TABLE  `zjhj_bd_formid` ADD INDEX `remains`(`remains`);

ALTER TABLE  `zjhj_bd_goods_attr` ADD INDEX `goods_id`(`goods_id`);
ALTER TABLE  `zjhj_bd_goods_attr` ADD INDEX `is_delete`(`is_delete`);

ALTER TABLE  `zjhj_bd_user_identity` ADD INDEX `user_id`(`user_id`);

ALTER TABLE  `zjhj_bd_check_in_user` ADD INDEX `user_id`(`user_id`);
ALTER TABLE  `zjhj_bd_check_in_user` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_check_in_user` ADD INDEX `is_delete`(`is_delete`);

ALTER TABLE  `zjhj_bd_attachment` ADD INDEX `attachment_group_id`(`attachment_group_id`);
ALTER TABLE  `zjhj_bd_attachment` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_attachment` ADD INDEX `mch_id`(`mch_id`);
ALTER TABLE  `zjhj_bd_attachment` ADD INDEX `type`(`type`);

ALTER TABLE  `zjhj_bd_attachment_group` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_attachment_group` ADD INDEX `mch_id`(`mch_id`);
ALTER TABLE  `zjhj_bd_attachment_group` ADD INDEX `type`(`type`);

ALTER TABLE  `zjhj_bd_balance_log` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_balance_log` ADD INDEX `user_id`(`user_id`);
ALTER TABLE  `zjhj_bd_balance_log` ADD INDEX `type`(`type`);

ALTER TABLE  `zjhj_bd_bargain_user_order` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_bargain_user_order` ADD INDEX `user_id`(`user_id`);
ALTER TABLE  `zjhj_bd_bargain_user_order` ADD INDEX `bargain_order_id`(`bargain_order_id`);

ALTER TABLE  `zjhj_bd_bonus_captain_log` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_bonus_captain_log` ADD INDEX `user_id`(`user_id`);

ALTER TABLE  `zjhj_bd_cart` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_cart` ADD INDEX `user_id`(`user_id`);

ALTER TABLE  `zjhj_bd_check_in_sign` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_check_in_sign` ADD INDEX `user_id`(`user_id`);

ALTER TABLE  `zjhj_bd_check_in_user_remind` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_check_in_user_remind` ADD INDEX `user_id`(`user_id`);
ALTER TABLE  `zjhj_bd_check_in_user_remind` ADD INDEX `is_remind`(`is_remind`);

ALTER TABLE  `zjhj_bd_core_queue_data` ADD INDEX `queue_id`(`queue_id`);
ALTER TABLE  `zjhj_bd_core_queue_data` ADD INDEX `token`(`token`);

ALTER TABLE  `zjhj_bd_coupon_mall_relation` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_coupon_mall_relation` ADD INDEX `user_coupon_id`(`user_coupon_id`);

ALTER TABLE  `zjhj_bd_goods_cats` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_goods_cats` ADD INDEX `mch_id`(`mch_id`);
ALTER TABLE  `zjhj_bd_goods_cats` ADD INDEX `parent_id`(`parent_id`);

ALTER TABLE  `zjhj_bd_goods_cat_relation` ADD INDEX `cat_id`(`cat_id`);

ALTER TABLE  `zjhj_bd_goods_member_price` ADD INDEX `goods_id`(`goods_id`);

ALTER TABLE  `zjhj_bd_integral_log` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_integral_log` ADD INDEX `user_id`(`user_id`);
ALTER TABLE  `zjhj_bd_integral_log` ADD INDEX `type`(`type`);

ALTER TABLE  `zjhj_bd_integral_mall_goods_attr` ADD INDEX `goods_id`(`goods_id`);
ALTER TABLE  `zjhj_bd_integral_mall_goods_attr` ADD INDEX `goods_attr_id`(`goods_attr_id`);

ALTER TABLE  `zjhj_bd_lottery_log` ADD INDEX `mall_id`(`mall_id`);

ALTER TABLE  `zjhj_bd_mall_goods` ADD INDEX `mall_id`(`mall_id`);

ALTER TABLE  `zjhj_bd_mall_setting` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_mall_setting` ADD INDEX `key`(`key`);

ALTER TABLE  `zjhj_bd_miaosha_goods` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_miaosha_goods` ADD INDEX `goods_id`(`goods_id`);
ALTER TABLE  `zjhj_bd_miaosha_goods` ADD INDEX `goods_warehouse_id`(`goods_warehouse_id`);

ALTER TABLE  `zjhj_bd_option` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_option` ADD INDEX `mch_id`(`mch_id`);
ALTER TABLE  `zjhj_bd_option` ADD INDEX `group`(`group`);
ALTER TABLE  `zjhj_bd_option` ADD INDEX `name`(`name`);

ALTER TABLE  `zjhj_bd_order` ADD INDEX `order_no`(`order_no`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `is_pay`(`is_pay`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `is_send`(`is_send`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `is_sale`(`is_sale`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `is_confirm`(`is_confirm`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `is_delete`(`is_delete`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `is_recycle`(`is_recycle`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `token`(`token`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `is_comment`(`is_comment`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `status`(`status`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `sale_status`(`sale_status`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `sign`(`sign`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `clerk_id`(`clerk_id`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `store_id`(`store_id`);
ALTER TABLE  `zjhj_bd_order` ADD INDEX `cancel_status`(`cancel_status`);

ALTER TABLE  `zjhj_bd_order_detail_express` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_order_detail_express` ADD INDEX `mch_id`(`mch_id`);
ALTER TABLE  `zjhj_bd_order_detail_express` ADD INDEX `order_id`(`order_id`);
ALTER TABLE  `zjhj_bd_order_detail_express` ADD INDEX `send_type`(`send_type`);

ALTER TABLE  `zjhj_bd_order_pay_result` ADD INDEX `order_id`(`order_id`);

ALTER TABLE  `zjhj_bd_payment_order` ADD INDEX `payment_order_union_id`(`payment_order_union_id`);
ALTER TABLE  `zjhj_bd_payment_order` ADD INDEX `order_no`(`order_no`);
ALTER TABLE  `zjhj_bd_payment_order` ADD INDEX `is_pay`(`is_pay`);
ALTER TABLE  `zjhj_bd_payment_order` ADD INDEX `pay_type`(`pay_type`);

ALTER TABLE  `zjhj_bd_payment_order_union` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_payment_order_union` ADD INDEX `user_id`(`user_id`);
ALTER TABLE  `zjhj_bd_payment_order_union` ADD INDEX `order_no`(`order_no`);
ALTER TABLE  `zjhj_bd_payment_order_union` ADD INDEX `is_pay`(`is_pay`);
ALTER TABLE  `zjhj_bd_payment_order_union` ADD INDEX `pay_type`(`pay_type`);

ALTER TABLE  `zjhj_bd_pond_log` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_pond_log` ADD INDEX `pond_id`(`pond_id`);
ALTER TABLE  `zjhj_bd_pond_log` ADD INDEX `user_id`(`user_id`);
ALTER TABLE  `zjhj_bd_pond_log` ADD INDEX `status`(`status`);
ALTER TABLE  `zjhj_bd_pond_log` ADD INDEX `type`(`type`);
ALTER TABLE  `zjhj_bd_pond_log` ADD INDEX `goods_id`(`goods_id`);
ALTER TABLE  `zjhj_bd_pond_log` ADD INDEX `order_id`(`order_id`);

ALTER TABLE  `zjhj_bd_quick_share_goods` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_quick_share_goods` ADD INDEX `goods_id`(`goods_id`);
ALTER TABLE  `zjhj_bd_quick_share_goods` ADD INDEX `status`(`status`);
ALTER TABLE  `zjhj_bd_quick_share_goods` ADD INDEX `is_top`(`is_top`);

ALTER TABLE  `zjhj_bd_share` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_share` ADD INDEX `user_id`(`user_id`);

ALTER TABLE  `zjhj_bd_share_cash_log` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_share_cash_log` ADD INDEX `user_id`(`user_id`);
ALTER TABLE  `zjhj_bd_share_cash_log` ADD INDEX `type`(`type`);

ALTER TABLE  `zjhj_bd_share_order` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_share_order` ADD INDEX `order_id`(`order_id`);
ALTER TABLE  `zjhj_bd_share_order` ADD INDEX `order_detail_id`(`order_detail_id`);
ALTER TABLE  `zjhj_bd_share_order` ADD INDEX `user_id`(`user_id`);
ALTER TABLE  `zjhj_bd_share_order` ADD INDEX `first_parent_id`(`first_parent_id`);
ALTER TABLE  `zjhj_bd_share_order` ADD INDEX `second_parent_id`(`second_parent_id`);
ALTER TABLE  `zjhj_bd_share_order` ADD INDEX `third_parent_id`(`third_parent_id`);

ALTER TABLE  `zjhj_bd_share_setting` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_share_setting` ADD INDEX `key`(`key`);

ALTER TABLE  `zjhj_bd_shopping_buys` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_shopping_buys` ADD INDEX `order_id`(`order_id`);
ALTER TABLE  `zjhj_bd_shopping_buys` ADD INDEX `user_id`(`user_id`);

ALTER TABLE  `zjhj_bd_template_record` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_template_record` ADD INDEX `user_id`(`user_id`);
ALTER TABLE  `zjhj_bd_template_record` ADD INDEX `status`(`status`);

ALTER TABLE  `zjhj_bd_user` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_user` ADD INDEX `mch_id`(`mch_id`);
ALTER TABLE  `zjhj_bd_user` ADD INDEX `username`(`username`);
ALTER TABLE  `zjhj_bd_user` ADD INDEX `access_token`(`access_token`);

ALTER TABLE  `zjhj_bd_user_coupon_auto` ADD INDEX `user_coupon_id`(`user_coupon_id`);
ALTER TABLE  `zjhj_bd_user_coupon_auto` ADD INDEX `auto_coupon_id`(`auto_coupon_id`);

ALTER TABLE  `zjhj_bd_user_coupon_center` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE  `zjhj_bd_user_coupon_center` ADD INDEX `user_coupon_id`(`user_coupon_id`);
ALTER TABLE  `zjhj_bd_user_coupon_center` ADD INDEX `user_id`(`user_id`);

ALTER TABLE  `zjhj_bd_user_info` ADD INDEX `platform_user_id`(`platform_user_id`);
ALTER TABLE  `zjhj_bd_user_info` ADD INDEX `temp_parent_id`(`temp_parent_id`);
EOF;
        sql_execute($sql);
    },

    '4.2.14' => function () {
    },

    '4.2.15' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_lottery`
MODIFY COLUMN `start_at`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开始时间' AFTER `stock`,
MODIFY COLUMN `end_at`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '结束时间' AFTER `start_at`;
EOF;
        sql_execute($sql);
    },

    '4.2.17' => function () {
    },

    '4.2.18' => function () {
    },

    '4.2.19' => function () {
    },

    '4.2.20' => function () {
        $sql = <<<EOF
alter table `zjhj_bd_order_detail_express` add `express_single_id` int(11) not null default 0 comment '电子面单ID';

ALTER TABLE `zjhj_bd_goods_cats`
ADD COLUMN `advert_open_type`  varchar(65) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '打开方式' AFTER `is_show`,
ADD COLUMN `advert_params`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '导航参数' AFTER `advert_open_type`;
EOF;
        sql_execute($sql);
    },

    '4.2.21' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_order_detail` ADD COLUMN `form_data` longtext NULL COMMENT '自定义表单提交的数据' AFTER `goods_no`;
ALTER TABLE `zjhj_bd_order_detail` ADD COLUMN `form_id` int(11) NOT NULL DEFAULT 0 COMMENT '自定义表单的id' AFTER `form_data`;
ALTER TABLE `zjhj_bd_coupon_auto_send` ADD COLUMN `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '领取人 0--所有人 1--指定用户', ADD COLUMN `user_list` longtext NULL COMMENT '指定用户id列表';
ALTER TABLE `zjhj_bd_share` ADD COLUMN `level` int(11) NOT NULL DEFAULT 0 COMMENT '分销商等级', ADD COLUMN `level_at` timestamp NULL DEFAULT '' COMMENT '成为分销商等级时间', ADD COLUMN `delete_first_show` tinyint(1) NOT NULL DEFAULT 0 COMMENT '删除后是否第一次展示';
CREATE TABLE `zjhj_bd_share_level` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `level` int(11) NOT NULL DEFAULT '1' COMMENT '分销等级1~100', `name` varchar(255) NOT NULL DEFAULT '' COMMENT '分销等级名称', `condition_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '升级条件：1--下线用户数|2--累计佣金|3--已提现佣金', `condition` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '下线用户数（人）|累计佣金数（元）|已提现佣金数（元）', `price_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '分销佣金类型：1--百分比|2--固定金额', `first` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '一级分销佣金数（元）', `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用', `is_delete` tinyint(1) NOT NULL DEFAULT '0', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, `second` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '二级分销佣金数（元）', `third` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '三级分销佣金数（元）', `is_auto_level` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用自动升级', `rule` varchar(255) NOT NULL DEFAULT '' COMMENT '等级说明', PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `zjhj_bd_goods` ADD COLUMN `form_id` int NOT NULL DEFAULT 0 COMMENT '自定义表单id 0--表示默认表单 -1--表示不使用表单';
ALTER TABLE `zjhj_bd_goods_share` ADD COLUMN `level` int(11) NOT NULL DEFAULT 0 COMMENT '分销商等级';
ALTER TABLE `zjhj_bd_pintuan_goods_share` ADD COLUMN `level` int(11) NOT NULL DEFAULT 0 COMMENT '分销商等级';
CREATE TABLE `zjhj_bd_form` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT '0', `mch_id` int(11) NOT NULL DEFAULT '0', `name` varchar(255) NOT NULL DEFAULT '', `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用', `data` longtext NOT NULL COMMENT '表单内容', `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认', `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `zjhj_bd_user_coupon` ADD COLUMN `discount_limit` decimal(10,2) NULL DEFAULT NULL COMMENT '折扣优惠上限';
ALTER TABLE `zjhj_bd_coupon` ADD COLUMN `discount_limit` decimal(10,2) NULL DEFAULT NULL COMMENT '折扣优惠上限';
ALTER TABLE `zjhj_bd_vip_card_setting` ADD COLUMN `share_level` text COMMENT '分销等级';
EOF;
        sql_execute($sql);
    },

    '4.2.22' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_goods_warehouse` MODIFY COLUMN `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商品名称' AFTER `mall_id`;
ALTER TABLE `zjhj_bd_share` ADD COLUMN `level` int(11) NOT NULL DEFAULT 0 COMMENT '分销商等级', ADD COLUMN `level_at` timestamp NULL DEFAULT '0000-00-00 00:00:00' COMMENT '成为分销商等级时间', ADD COLUMN `delete_first_show` tinyint(1) NOT NULL DEFAULT 0 COMMENT '删除后是否第一次展示';
EOF;
        sql_execute($sql);
    },

    '4.2.23' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_form` ADD COLUMN `value` longtext NOT NULL;
EOF;
        sql_execute($sql);
    },

    '4.2.24' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_form` MODIFY COLUMN `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '表单内容';
EOF;
        sql_execute($sql);
    },

    '4.2.25' => function () {
    },

    '4.2.27' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_order_detail` MODIFY COLUMN `form_id` int(11) NULL DEFAULT 0 COMMENT '自定义表单的id' AFTER `form_data`;
EOF;
        sql_execute($sql);
    },

    '4.2.28' => function () {
        $sql = <<<EOF
CREATE TABLE `zjhj_bd_wxapp_subscribe` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `tpl_name` varchar(65) NOT NULL DEFAULT '', `tpl_id` varchar(255) NOT NULL DEFAULT '', `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信订阅消息';
EOF;
        sql_execute($sql);
    },

    '4.2.29' => function () {
    },

    '4.2.30' => function () {
        $sql = <<<EOF
CREATE TABLE `zjhj_bd_core_template` ( `id` int(11) NOT NULL AUTO_INCREMENT, `template_id` int(11) NOT NULL DEFAULT '0' COMMENT '模板id', `name` varchar(255) NOT NULL DEFAULT '' COMMENT '模板名称', `author` varchar(255) NOT NULL DEFAULT '' COMMENT '作者', `price` decimal(10,0) NOT NULL DEFAULT '0' COMMENT '价格', `pics` longtext NOT NULL, `data` longtext NOT NULL COMMENT '数据', `order_no` varchar(255) NOT NULL DEFAULT '' COMMENT '订单号', `version` varchar(255) NOT NULL DEFAULT '' COMMENT '版本号', `type` varchar(255) NOT NULL DEFAULT '' COMMENT 'home--首页布局 diy--DIY模板', `detail` longtext NOT NULL, `is_delete` tinyint(1) NOT NULL, `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `zjhj_bd_core_template_edit` ( `id` int(11) NOT NULL AUTO_INCREMENT, `template_id` int(11) NOT NULL DEFAULT '0' COMMENT '模板id', `name` varchar(255) NOT NULL DEFAULT '' COMMENT '修改后名称', `price` decimal(10,0) NOT NULL DEFAULT '0' COMMENT '修改后价格', PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `zjhj_bd_address` ADD INDEX `user_id`(`user_id`);
ALTER TABLE `zjhj_bd_vip_card_setting` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE `zjhj_bd_vip_card_appoint_goods` ADD INDEX `goods_id`(`goods_id`);
ALTER TABLE `zjhj_bd_mall_members` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE `zjhj_bd_mall_members` ADD INDEX `level`(`level`);
ALTER TABLE `zjhj_bd_printer_setting` ADD INDEX `mall_id`(`mall_id`);
ALTER TABLE `zjhj_bd_printer_setting` ADD INDEX `mch_id`(`mch_id`);
ALTER TABLE `zjhj_bd_printer_setting` ADD INDEX `status`(`status`);
ALTER TABLE `zjhj_bd_printer_setting` ADD INDEX `store_id`(`store_id`);
EOF;
        sql_execute($sql);
    },

    '4.2.31' => function () {
        $sql = <<<EOF
CREATE TABLE `zjhj_bd_import_goods` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `mch_id` int(11) NOT NULL DEFAULT '0', `user_id` int(11) NOT NULL COMMENT '操作账户ID', `status` tinyint(4) NOT NULL COMMENT '导入状态|1.全部失败|2.部分失败|3.全部成功', `file_name` varchar(191) NOT NULL DEFAULT '' COMMENT '导入文件名', `goods_count` int(11) NOT NULL, `success_count` int(11) NOT NULL, `error_count` int(11) NOT NULL, `created_at` timestamp NOT NULL, `updated_at` timestamp NOT NULL, `deleted_at` timestamp NOT NULL, `is_delete` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
alter table `zjhj_bd_order_comments` add `is_top` tinyint(1) default 0 not null comment '是否置顶0.否|1.是';
EOF;
        sql_execute($sql);
    },

    '4.2.32' => function () {
    },

    '4.2.35' => function () {
    },

    '4.2.36' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_gift_log` ADD COLUMN `bless_music` VARCHAR(200) NULL COMMENT '祝福语音' AFTER `bless_word`;
EOF;
        sql_execute($sql);
    },

    '4.2.38' => function () {
        $sql = <<<EOF
alter table zjhj_bd_goods change app_share_title app_share_title varchar(65) character set utf8mb4 not null default '' comment '自定义分享标题';
ALTER TABLE `zjhj_bd_advance_goods` CHARACTER SET = utf8mb4, COLLATE = utf8mb4_general_ci;
ALTER TABLE `zjhj_bd_advance_goods_attr` CHARACTER SET = utf8mb4, COLLATE = utf8mb4_general_ci;
CREATE TABLE `zjhj_bd_stock_bonus_log` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT 0, `bonus_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1按周，2按月', `bonus_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '分红金额', `bonus_rate` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '当时的分红比例', `order_num` int(11) NOT NULL DEFAULT 0 COMMENT '分红订单数', `stock_num` int(11) NOT NULL DEFAULT 0 COMMENT '当时股东人数', `start_time` timestamp(0) NOT NULL COMMENT '分红时间段-开始时间', `end_time` timestamp(0) NOT NULL COMMENT '分红时间段-结束时间', `created_at` timestamp(0) NOT NULL, `updated_at` timestamp(0) NOT NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;
CREATE TABLE `zjhj_bd_stock_cash` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `order_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '订单号', `price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '提现金额', `service_charge` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '提现手续费（%）', `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '提现方式 auto--自动打款 wechat--微信打款 alipay--支付宝打款 bank--银行转账 balance--打款到余额', `extra` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '额外信息 例如微信账号、支付宝账号等', `status` int(11) NOT NULL DEFAULT 0 COMMENT '提现状态 0--申请 1--同意 2--已打款 3--驳回', `is_delete` int(11) NOT NULL DEFAULT 0, `created_at` datetime(0) NOT NULL, `updated_at` datetime(0) NOT NULL, `deleted_at` datetime(0) NOT NULL, `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '提现记录表' ROW_FORMAT = Dynamic;
CREATE TABLE `zjhj_bd_stock_cash_log` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `type` int(11) NOT NULL DEFAULT 1 COMMENT '类型 1--收入 2--支出', `price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '变动佣金', `desc` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL, `custom_desc` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL, `level_id` int(11) NULL DEFAULT 0 COMMENT '当时的股东等级', `level_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, `order_num` int(11) NULL DEFAULT 0, `bonus_rate` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '当时的分红比例', `bonus_id` int(11) NULL DEFAULT 0 COMMENT '股东完成分红记录ID', `is_delete` int(11) NOT NULL DEFAULT 0, `created_at` datetime(0) NOT NULL, `updated_at` datetime(0) NOT NULL, `deleted_at` datetime(0) NOT NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '分红日志' ROW_FORMAT = Dynamic;
CREATE TABLE `zjhj_bd_stock_level` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT 0, `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '等级名称', `bonus_rate` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '分红比例', `condition` int(11) NOT NULL DEFAULT 0 COMMENT '升级条件，0不自动升级', `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否默认等级，0否1是', `is_delete` tinyint(1) NOT NULL DEFAULT 0, `deleted_at` timestamp(0) NOT NULL, `created_at` timestamp(0) NOT NULL, `updated_at` timestamp(0) NOT NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '股东等级表' ROW_FORMAT = Dynamic;
CREATE TABLE `zjhj_bd_stock_level_up` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT 0, `type` tinyint(2) NOT NULL DEFAULT 1 COMMENT '1下线总人数，2累计佣金总额，3已提现佣金总额，4分销订单总数，5分销订单总金额', `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '股东等级升级条件' ROW_FORMAT = Dynamic;
CREATE TABLE `zjhj_bd_stock_order` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT 0, `order_id` int(11) NOT NULL DEFAULT 0, `total_pay_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '订单实付金额', `is_bonus` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1已分红，0未分红', `bonus_time` timestamp(0) NOT NULL COMMENT '分红时间', `bonus_id` int(11) NOT NULL DEFAULT 0 COMMENT '股东完成分红记录ID', `is_delete` tinyint(1) NOT NULL DEFAULT 0, `deleted_at` timestamp(0) NOT NULL, `created_at` timestamp(0) NOT NULL, `updated_at` timestamp(0) NOT NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '分红池' ROW_FORMAT = Dynamic;
CREATE TABLE `zjhj_bd_stock_setting` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL, `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, `created_at` timestamp(0) NOT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '创建时间', `updated_at` timestamp(0) NOT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间', `is_delete` int(11) NOT NULL DEFAULT 0 COMMENT '是否删除 0--未删除 1--已删除', `deleted_at` timestamp(0) NOT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '删除时间', PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '股东分红设置' ROW_FORMAT = Dynamic;
CREATE TABLE `zjhj_bd_stock_user` ( `id` int(11) NOT NULL AUTO_INCREMENT, `mall_id` int(11) NOT NULL DEFAULT 0, `user_id` int(11) NOT NULL DEFAULT 0, `level_id` int(11) NOT NULL DEFAULT 0 COMMENT '对应等级表ID', `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '-2被拒或移除后再次申请没提交 -1移除 0审核中，1同意，2拒绝', `is_delete` tinyint(1) NOT NULL DEFAULT 0, `deleted_at` timestamp(0) NOT NULL, `created_at` timestamp(0) NOT NULL, `updated_at` timestamp(0) NOT NULL, `applyed_at` timestamp(0) NOT NULL COMMENT '申请时间', `agreed_at` timestamp(0) NOT NULL COMMENT '审核时间', PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '股东表' ROW_FORMAT = Dynamic;
CREATE TABLE `zjhj_bd_stock_user_info` ( `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL DEFAULT 0, `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '股东姓名', `phone` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '股东手机号', `all_bonus` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '累计分红', `total_bonus` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '当前分红', `out_bonus` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '已提现分红', `remark` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注', `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '拒绝理由', `created_at` timestamp(0) NOT NULL, `updated_at` timestamp(0) NOT NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '股东信息表' ROW_FORMAT = Dynamic;
EOF;
        sql_execute($sql);
    },

    '4.2.39' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_bonus_setting` 
MODIFY COLUMN `updated_at` timestamp(0) NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间' AFTER `created_at`,
MODIFY COLUMN `deleted_at` timestamp(0) NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '删除时间' AFTER `is_delete`;
ALTER TABLE `zjhj_bd_order` ADD INDEX `index2`(`mall_id`, `is_delete`, `cancel_status`);
ALTER TABLE `zjhj_bd_order` ADD INDEX `index3`(`mall_id`, `is_delete`, `cancel_status`, `is_pay`);
ALTER TABLE `zjhj_bd_order` ADD INDEX `index4`(`mall_id`, `is_delete`, `cancel_status`, `pay_type`);
ALTER TABLE `zjhj_bd_order` ADD INDEX `index5`(`mall_id`, `is_delete`, `cancel_status`, `is_pay`, `pay_type`);
ALTER TABLE `zjhj_bd_order_detail` ADD INDEX `index1`(`goods_id`, `is_refund`, `order_id`);
EOF;
        sql_execute($sql);
    },

    '4.2.40' => function () {
        $sql = <<<EOF
alter table zjhj_bd_store add `is_all_day` tinyint(1) not null default 0 comment '是否全天营业0.否|1.是';
EOF;
        sql_execute($sql);
    },

    '4.2.42' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_stock_bonus_log` MODIFY COLUMN `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分红时间段-开始时间' AFTER `stock_num`, MODIFY COLUMN `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分红时间段-结束时间' AFTER `start_time`;
ALTER TABLE `zjhj_bd_stock_order` MODIFY COLUMN `bonus_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分红时间' AFTER `is_bonus`;
CREATE TABLE `zjhj_bd_order_send_template` ( `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT, `mall_id` INT ( 11 ) NOT NULL, `mch_id` INT ( 11 ) NOT NULL DEFAULT '0', `name` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT '发货单名称', `cover_pic` VARCHAR ( 255 ) NOT NULL DEFAULT '' COMMENT '缩略图', `params` text NOT NULL COMMENT '模板参数', `is_default` TINYINT ( 1 ) NOT NULL DEFAULT '0' COMMENT '是否为默认模板0.否|1.是', `created_at` TIMESTAMP NOT NULL, `updated_at` TIMESTAMP NOT NULL, `deleted_at` TIMESTAMP NOT NULL, `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0', PRIMARY KEY ( `id` ) ) ENGINE = INNODB DEFAULT CHARSET = utf8mb4;
CREATE TABLE `zjhj_bd_order_send_template_address` ( `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT, `mall_id` INT ( 11 ) NOT NULL, `mch_id` INT ( 11 ) NOT NULL, `name` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT '网点名称', `username` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT '联系人', `mobile` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT '联系电话', `code` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT '网点邮编', `address` VARCHAR ( 255 ) NOT NULL DEFAULT '' COMMENT '地址', `created_at` TIMESTAMP NOT NULL, `updated_at` TIMESTAMP NOT NULL, `deleted_at` TIMESTAMP NOT NULL, `is_delete` TINYINT ( 1 ) NOT NULL DEFAULT '0', PRIMARY KEY ( `id` ) ) ENGINE = INNODB DEFAULT CHARSET = utf8mb4;
EOF;
        sql_execute($sql);
    },

    '4.2.43' => function () {
    },

    '4.2.45' => function () {
    },

    '4.2.46' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_gift_log` CHARSET=utf8mb4;
ALTER TABLE `zjhj_bd_gift_log` MODIFY COLUMN `bless_word` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '祝福语' AFTER `open_type`;
EOF;
        sql_execute($sql);
    },

    '4.2.47' => function () {
    },

    '4.2.48' => function () {
        $sql = <<<EOF
ALTER TABLE `zjhj_bd_printer_setting` ADD COLUMN `big` int(11) NOT NULL DEFAULT 0 COMMENT '放大倍数' AFTER `deleted_at`, ADD COLUMN `show_type` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '打印参数 attr 规格 goods_no 货号 form_data 下单表单' AFTER `big`;
EOF;
        sql_execute($sql);
    },

];
