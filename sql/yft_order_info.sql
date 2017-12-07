/*
Navicat MySQL Data Transfer

Source Server         : 本地测试
Source Server Version : 50557
Source Host           : localhost:3306
Source Database       : panghu

Target Server Type    : MYSQL
Target Server Version : 50557
File Encoding         : 65001

Date: 2017-12-07 11:04:39
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for yft_order_info
-- ----------------------------
DROP TABLE IF EXISTS `yft_order_info`;
CREATE TABLE `yft_order_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `ss_order` varchar(50) DEFAULT NULL,
  `yft_order` varchar(50) DEFAULT NULL,
  `price` varchar(10) DEFAULT NULL,
  `state` tinyint(1) DEFAULT NULL COMMENT '0代表未支付，1代表已支付',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
