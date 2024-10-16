--
-- Update sql for MailWizz EMA from version 2.4.9 to 2.5.0
--

--
-- Table structure for table `campaign_tracking_ignore_list`
--

DROP TABLE IF EXISTS `campaign_tracking_ignore_list`;
CREATE TABLE IF NOT EXISTS `campaign_tracking_ignore_list`(
  `id`            bigint(20)   NOT NULL AUTO_INCREMENT,
  `action`        enum('open', 'click') NOT NULL DEFAULT 'click',
  `status`        char(10) NOT NULL DEFAULT 'active',
  `ip_address`    varchar(45)  NOT NULL,
  `reason`        varchar(255) NULL,
  `date_added`    DATETIME     NOT NULL,
  `last_updated`  DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address_UNIQUE` (`ip_address`)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT = 1;

-- --------------------------------------------------------

ALTER TABLE `campaign_option`
    ADD `smart_open_tracking` ENUM('no','yes') NOT NULL DEFAULT 'no' AFTER `url_tracking`,
    ADD `smart_click_tracking` ENUM('no','yes') NOT NULL DEFAULT 'no' AFTER `smart_open_tracking`;
