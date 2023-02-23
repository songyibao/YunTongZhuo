/*
 Navicat Premium Data Transfer

 Source Server         : 159.75.122.204
 Source Server Type    : MariaDB
 Source Server Version : 100327
 Source Host           : 159.75.122.204:3306
 Source Schema         : YunTongZhuo

 Target Server Type    : MariaDB
 Target Server Version : 100327
 File Encoding         : 65001

 Date: 27/11/2021 21:02:32
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for D_apply
-- ----------------------------
DROP TABLE IF EXISTS `D_apply`;
CREATE TABLE `D_apply` (
  `id` int(11) NOT NULL COMMENT '被邀请者id',
  `from` int(11) NOT NULL COMMENT '邀请者id',
  `create_time` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '邀请时间',
  `comment` longtext DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`,`from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for Deskmate
-- ----------------------------
DROP TABLE IF EXISTS `Deskmate`;
CREATE TABLE `Deskmate` (
  `id` int(11) NOT NULL,
  `from` int(11) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `plan` longtext DEFAULT '未制定学习计划',
  `tell` longtext DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT NULL,
  `to_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`,`from`) USING BTREE,
  KEY `deskmate_ibfk_2` (`from`),
  CONSTRAINT `deskmate_ibfk_1` FOREIGN KEY (`id`) REFERENCES `User` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deskmate_ibfk_2` FOREIGN KEY (`from`) REFERENCES `User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for Study
-- ----------------------------
DROP TABLE IF EXISTS `Study`;
CREATE TABLE `Study` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `did` int(11) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `is_end` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk1` (`uid`),
  CONSTRAINT `fk1` FOREIGN KEY (`uid`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for User
-- ----------------------------
DROP TABLE IF EXISTS `User`;
CREATE TABLE `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `nickName` varchar(20) DEFAULT NULL COMMENT '用户昵称',
  `tel` varchar(11) DEFAULT NULL COMMENT '手机号',
  `openid` varchar(50) DEFAULT NULL COMMENT '微信union id',
  `avatarUrl` longtext DEFAULT NULL COMMENT '头像',
  `create_time` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '注册时间',
  `update_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '更新时间',
  `have_d` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否有同桌',
  `signature` longtext DEFAULT '暂无',
  `intro` longtext DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
