/**
 * @author		Can Berkol
 * @author		Said İmamoğlu
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        23.12.2015
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
  UNIQUE KEY `idxULanguageId` (`id`) USING BTREE,
  UNIQUE KEY `idxULanguageUrlKey` (`url_key`,`site`) USING BTREE,
  UNIQUE KEY `idxULanguageIsoCode` (`iso_code`,`site`) USING BTREE,
  KEY `idxFSiteOfLanguage` (`site`) USING BTREE,
  KEY `idxNLanguageSchema` (`schema`,`site`) USING BTREE,
  CONSTRAINT `idxFSiteOfLanguage` FOREIGN KEY (`site`) REFERENCES `site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for translation
-- ----------------------------
DROP TABLE IF EXISTS `translation`;
CREATE TABLE `translation` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'System given id.',
  `domain` varchar(255) COLLATE utf8_turkish_ci DEFAULT NULL COMMENT 'Domain / file name of translation.',
  `key` varchar(255) COLLATE utf8_turkish_ci NOT NULL COMMENT 'Translation key.',
  `date_added` datetime NOT NULL COMMENT 'Date when the translation is added.',
  `date_updated` datetime NOT NULL COMMENT 'Date when the translation is last updated.',
  `date_removed` datetime DEFAULT NULL COMMENT 'Date when the entry is marked as removed.',
  `site` int(10) unsigned DEFAULT NULL COMMENT 'Site that translation belongs to.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxUTranslationId` (`id`) USING BTREE,
  UNIQUE KEY `idxUTranslationKeu` (`key`,`site`) USING BTREE,
  KEY `iidxNTranslationDateAdded` (`date_added`) USING BTREE,
  KEY `idxNTranslationDateUpdated` (`date_updated`) USING BTREE,
  KEY `idxFSiteOfTranslation` (`site`) USING BTREE,
  KEY `idxNTranslationDateRemoved` (`date_removed`),
  CONSTRAINT `idxFSiteOfTranslation` FOREIGN KEY (`site`) REFERENCES `translation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for translation_localization
-- ----------------------------
DROP TABLE IF EXISTS `translation_localization`;
CREATE TABLE `translation_localization` (
  `translation` int(20) unsigned NOT NULL COMMENT 'Translated key.',
  `language` int(5) unsigned NOT NULL COMMENT 'Translation language.',
  `phrase` text COLLATE utf8_turkish_ci NOT NULL COMMENT 'Translation phrase.',
  PRIMARY KEY (`translation`,`language`),
  UNIQUE KEY `idxUTranslationLocalization` (`translation`,`language`) USING BTREE,
  KEY `idxFTranslationLocalizationLanguage` (`language`) USING BTREE,
  KEY `idxFLocalizedTranslation` (`translation`) USING BTREE,
  CONSTRAINT `idxFLocalizedTranslation` FOREIGN KEY (`translation`) REFERENCES `translation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `idxFTranslationLocalizationLanguage` FOREIGN KEY (`language`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;
