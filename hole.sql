/*
 Navicat Premium Data Transfer

 Source Server         : MAMP
 Source Server Type    : MySQL
 Source Server Version : 50638
 Source Host           : localhost:3306
 Source Schema         : hole

 Target Server Type    : MySQL
 Target Server Version : 50638
 File Encoding         : 65001

 Date: 10/05/2018 14:33:19
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for hole_comment
-- ----------------------------
DROP TABLE IF EXISTS `hole_comment`;
CREATE TABLE `hole_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(800) NOT NULL DEFAULT '' COMMENT '用户发送评论内容',
  `like_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论点赞数',
  `content_id` int(10) unsigned NOT NULL COMMENT '用户评论树洞内容对象id',
  `user_id` int(10) unsigned NOT NULL COMMENT '发送内容用户的id',
  `hide` tinyint(1) unsigned NOT NULL COMMENT '是否匿名，0：匿名，1：实名',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for hole_content
-- ----------------------------
DROP TABLE IF EXISTS `hole_content`;
CREATE TABLE `hole_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(800) NOT NULL DEFAULT '' COMMENT '用户发送树洞内容',
  `user_id` int(10) unsigned NOT NULL COMMENT '发送内容用户的id',
  `tag` varchar(50) NOT NULL DEFAULT '' COMMENT '发送内容的标签',
  `flag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '实现置顶之类的功能',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否匿名，0：匿名，1：实名',
  `like_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞人数',
  `dislike_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点踩人数',
  `report_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '举报人数',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for hole_operate
-- ----------------------------
DROP TABLE IF EXISTS `hole_operate`;
CREATE TABLE `hole_operate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户操作类型：1.点赞，2.点踩，3.评论，4.给评论点赞',
  `from_user` int(10) unsigned NOT NULL COMMENT '操作用户的id',
  `to_user` int(10) unsigned NOT NULL COMMENT '要通知用户的id',
  `flag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户是否查看操作通知，0：未查看，1：查看',
  `object_id` int(10) unsigned NOT NULL COMMENT '用户操作对象id，type=4时为评论id，其余为内容id',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for hole_user
-- ----------------------------
DROP TABLE IF EXISTS `hole_user`;
CREATE TABLE `hole_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(10) NOT NULL DEFAULT '' COMMENT '用户微信昵称',
  `avatar` varchar(300) NOT NULL DEFAULT '' COMMENT '用户微信头像',
  `gender` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户性别，0：未知，1：男，2：女',
  `openid` varchar(100) NOT NULL DEFAULT '' COMMENT '微信返回的用户的openid',
  `session_key` varchar(100) NOT NULL DEFAULT '' COMMENT '微信返回的用户的session_key',
  `3rd_session` varchar(100) NOT NULL DEFAULT '' COMMENT '计算得到用户标识',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
