/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : bod_core

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2015-04-27 15:48:06
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for language
-- ----------------------------
DROP TABLE IF EXISTS `language`;
CREATE TABLE `language` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'System given id.',
  `name` varchar(155) COLLATE utf8_turkish_ci NOT NULL COMMENT 'Local name of language.',
  `url_key` varchar(255) COLLATE utf8_turkish_ci NOT NULL COMMENT 'URL segment key.',
  `iso_code` varchar(7) COLLATE utf8_turkish_ci DEFAULT NULL COMMENT 'ISO code of the language. Up to 7 chars.',
  `schema` char(3) COLLATE utf8_turkish_ci NOT NULL DEFAULT 'ltr' COMMENT 'ltr:left to right, rtl: right to left, btt: bottom to top; ttb: top to bottom.',
  `site` int(10) unsigned DEFAULT NULL COMMENT 'Site that language resides in.',
  `status` char(1) COLLATE utf8_turkish_ci NOT NULL DEFAULT 'a' COMMENT 'a:active, i:inactive',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_u_language_id` (`id`) USING BTREE,
  UNIQUE KEY `idx_u_language_url_key` (`url_key`,`site`) USING BTREE,
  UNIQUE KEY `idx_u_language_iso_code` (`iso_code`,`site`) USING BTREE,
  KEY `idx_n_language_schema` (`schema`,`site`) USING BTREE,
  KEY `idx_f_language_site` (`site`) USING BTREE,
  CONSTRAINT `idx_f_language_site` FOREIGN KEY (`site`) REFERENCES `site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for translation
-- ----------------------------
DROP TABLE IF EXISTS `translation`;
CREATE TABLE `translation` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'System given id.',
  `domain` varchar(255) COLLATE utf8_turkish_ci DEFAULT NULL COMMENT 'Domain / file name of translation.',
  `key` varchar(255) COLLATE utf8_turkish_ci NOT NULL COMMENT 'Translation key.',
  `instructions` varchar(255) COLLATE utf8_turkish_ci DEFAULT NULL COMMENT 'Translation instructions if exists.',
  `date_added` datetime NOT NULL COMMENT 'Date when the translation is added.',
  `date_updated` datetime NOT NULL COMMENT 'Date when the translation is last updated.',
  `site` int(10) unsigned DEFAULT NULL COMMENT 'Site that translation belongs to.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_u_translation_id` (`id`) USING BTREE,
  UNIQUE KEY `idx_u_translation_key` (`key`,`site`) USING BTREE,
  KEY `idx_n_translation_date_added` (`date_added`) USING BTREE,
  KEY `idx_n_translation_date_updated` (`date_updated`) USING BTREE,
  KEY `idx_f_translation_site` (`site`) USING BTREE,
  CONSTRAINT `idx_f_translation_site` FOREIGN KEY (`site`) REFERENCES `translation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for translation_localization
-- ----------------------------
DROP TABLE IF EXISTS `translation_localization`;
CREATE TABLE `translation_localization` (
  `translation` int(20) unsigned NOT NULL COMMENT 'Translated key.',
  `language` int(5) unsigned NOT NULL COMMENT 'Translation language.',
  `phrase` text COLLATE utf8_turkish_ci NOT NULL COMMENT 'Translation phrase.',
  `date_added` datetime NOT NULL COMMENT 'Date when the phrase is added.',
  `date_updated` datetime DEFAULT NULL COMMENT 'Date when the phrase last updated.',
  PRIMARY KEY (`translation`,`language`),
  UNIQUE KEY `idx_u_translation_localization` (`translation`,`language`) USING BTREE,
  KEY `idx_n_translation_localization_date_added` (`date_added`) USING BTREE,
  KEY `idx_n_translation_localization_date_updated` (`date_updated`) USING BTREE,
  KEY `idx_f_translation_localization_language` (`language`) USING BTREE,
  KEY `idx_f_translation_localication_translation` (`translation`) USING BTREE,
  CONSTRAINT `idx_f_translation_localization_language` FOREIGN KEY (`language`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `idx_f_translation_localization_translation` FOREIGN KEY (`translation`) REFERENCES `translation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;
