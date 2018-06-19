/*
 Navicat Premium Data Transfer

 Source Server         : MAMP
 Source Server Type    : MySQL
 Source Server Version : 50638
 Source Host           : localhost:3306
 Source Schema         : holeH5

 Target Server Type    : MySQL
 Target Server Version : 50638
 File Encoding         : 65001

 Date: 19/06/2018 22:20:20
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
  `dislike_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论点踩人数',
  `content_id` int(10) unsigned NOT NULL COMMENT '帖子id',
  `userV` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送评论用户的id',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否匿名，0：匿名，1：实名',
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
  `userV` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送内容用户的id，用户可以完全匿名发送',
  `userT` varchar(50) NOT NULL DEFAULT '' COMMENT '未登录用户标识',
  `verified` int(10) NOT NULL DEFAULT '0' COMMENT '审帖数',
  `tag` varchar(50) NOT NULL DEFAULT '' COMMENT '发送内容的标签',
  `flag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1:置顶',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否匿名，0：匿名，1：实名',
  `like_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞人数',
  `dislike_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点踩人数',
  `comment_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论人数',
  `report_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '举报人数',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户是否删除了帖子，0：未删除，1：删除',
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
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户操作类型：1.点赞，2.点踩，3.评论，4.举报，5.给评论点赞，6.给评论点踩，7.给帖子审阅',
  `identity` varchar(50) NOT NULL DEFAULT '' COMMENT '操作用户标识，没有登录的情况下点赞识别',
  `from_user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作用户的id',
  `to_user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '要通知用户的id',
  `flag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户是否查看操作通知，0：未查看，1：查看',
  `object_id` int(10) unsigned NOT NULL COMMENT '用户操作对象id',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for hole_user
-- ----------------------------
DROP TABLE IF EXISTS `hole_user`;
CREATE TABLE `hole_user` (
  `identity` varchar(50) NOT NULL DEFAULT '' COMMENT '用户加密标识',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(10) NOT NULL DEFAULT '' COMMENT '用户昵称',
  `avatar` varchar(300) NOT NULL DEFAULT '' COMMENT '用户头像',
  `gender` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户性别，0：未知，1：男，2：女',
  `mail` varchar(50) NOT NULL DEFAULT '' COMMENT '用户的邮箱',
  `password` varchar(50) NOT NULL DEFAULT '' COMMENT '用户密码，md5',
  `activate` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：未激活，1：激活',
  `captcha` varchar(50) NOT NULL DEFAULT '' COMMENT '用户找回密码验证码',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identity` (`identity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
