/*
 * 家庭点餐小程序业务表（与 migration 一致）
 * 表前缀：jt_jiating_
 */
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `jt_jiating_order`;
DROP TABLE IF EXISTS `jt_jiating_food_spec`;
DROP TABLE IF EXISTS `jt_jiating_food`;
DROP TABLE IF EXISTS `jt_jiating_family_member`;
DROP TABLE IF EXISTS `jt_jiating_family`;
DROP TABLE IF EXISTS `jt_jiating_user`;

CREATE TABLE `jt_jiating_user` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `openid` varchar(100) NOT NULL DEFAULT '' COMMENT '微信唯一openid',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '用户昵称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '用户头像地址',
  `token` varchar(255) NOT NULL DEFAULT '' COMMENT '登录令牌',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_openid` (`openid`),
  KEY `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

CREATE TABLE `jt_jiating_family` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '家庭ID',
  `family_name` varchar(50) NOT NULL DEFAULT '' COMMENT '家庭名称',
  `invite_code` varchar(6) NOT NULL DEFAULT '' COMMENT '6位邀请码',
  `admin_uid` int unsigned NOT NULL DEFAULT 0 COMMENT '管理员用户ID',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_invite_code` (`invite_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='家庭表';

CREATE TABLE `jt_jiating_family_member` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `family_id` int unsigned NOT NULL DEFAULT 0 COMMENT '家庭ID',
  `uid` int unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '加入时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_family_uid` (`family_id`,`uid`),
  KEY `idx_family_id` (`family_id`),
  KEY `idx_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='家庭成员关联表';

CREATE TABLE `jt_jiating_food` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '菜品ID',
  `family_id` int unsigned NOT NULL DEFAULT 0 COMMENT '所属家庭ID',
  `food_name` varchar(50) NOT NULL DEFAULT '' COMMENT '菜品名称',
  `food_img` varchar(255) NOT NULL DEFAULT '' COMMENT '菜品图片地址',
  `category` varchar(20) NOT NULL DEFAULT '' COMMENT '菜品分类',
  `cook_uids` varchar(100) NOT NULL DEFAULT '' COMMENT '可烹饪成员ID，逗号分隔',
  `create_uid` int unsigned NOT NULL DEFAULT 0 COMMENT '创建人ID',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_family_id` (`family_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='菜品表';

CREATE TABLE `jt_jiating_food_spec` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '规格ID',
  `food_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联菜品ID',
  `spec_name` varchar(30) NOT NULL DEFAULT '' COMMENT '规格名称',
  `spec_value` varchar(100) NOT NULL DEFAULT '' COMMENT '规格选项值',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_food_id` (`food_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='菜品规格表';

CREATE TABLE `jt_jiating_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '点餐ID',
  `family_id` int unsigned NOT NULL DEFAULT 0 COMMENT '家庭ID',
  `food_id` int unsigned NOT NULL DEFAULT 0 COMMENT '菜品ID',
  `select_spec` text NOT NULL COMMENT '用户选中规格JSON',
  `order_uid` int unsigned NOT NULL DEFAULT 0 COMMENT '点餐人ID',
  `cook_uid` int unsigned NOT NULL DEFAULT 0 COMMENT '指派烹饪人ID',
  `meal_type` varchar(10) NOT NULL DEFAULT '' COMMENT '用餐时段：早/中/晚',
  `order_date` varchar(20) NOT NULL DEFAULT '' COMMENT '点餐日期',
  `status` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '状态1待制作2制作中3已完成4已取消',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '点餐时间',
  PRIMARY KEY (`id`),
  KEY `idx_family_id` (`family_id`),
  KEY `idx_order_date` (`order_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='点餐记录表';

SET FOREIGN_KEY_CHECKS = 1;
