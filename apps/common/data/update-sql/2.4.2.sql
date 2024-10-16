--
-- Update sql for MailWizz EMA from version 2.4.1 to 2.4.2
--

--
-- Table structure for table `campaign_delivery_count_history`
--
DROP TABLE IF EXISTS `campaign_delivery_count_history`;
CREATE TABLE IF NOT EXISTS `campaign_delivery_count_history` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `customer_id` int(11) NOT NULL,
    `campaign_id` int(11) NOT NULL,

    `total` int(11) NOT NULL DEFAULT 0,

    `success_total` int(11) NOT NULL DEFAULT 0,
    `error_total` int(11) NOT NULL DEFAULT 0,
    `giveup_total` int(11) NOT NULL DEFAULT 0,
    `blacklisted_total` int(11) NOT NULL DEFAULT 0,
    `dp_reject_total` int(11) NOT NULL DEFAULT 0,

    `hard_bounce_total` int(11) NOT NULL DEFAULT 0,
    `soft_bounce_total` int(11) NOT NULL DEFAULT 0,
    `internal_bounce_total` int(11) NOT NULL DEFAULT 0,
    `complaint_total` int(11) NOT NULL DEFAULT 0,

    `success_hourly` int(11) NOT NULL DEFAULT 0,
    `error_hourly` int(11) NOT NULL DEFAULT 0,
    `giveup_hourly` int(11) NOT NULL DEFAULT 0,
    `blacklisted_hourly` int(11) NOT NULL DEFAULT 0,
    `dp_reject_hourly` int(11) NOT NULL DEFAULT 0,

    `hard_bounce_hourly` int(11) NOT NULL DEFAULT 0,
    `soft_bounce_hourly` int(11) NOT NULL DEFAULT 0,
    `internal_bounce_hourly` int(11) NOT NULL DEFAULT 0,
    `complaint_hourly` int(11) NOT NULL DEFAULT 0,

    `date_added` datetime NOT NULL,
PRIMARY KEY (`id`),
KEY `fk_campaign_delivery_count_history_campaign1_idx` (`campaign_id`),
KEY `fk_campaign_delivery_count_history_customer1_idx` (`customer_id`),
KEY `customer_id_campaign_id_date_added` (`customer_id`,`campaign_id`,`date_added`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;


--
-- Constraints for table `campaign_delivery_count_history`
--
ALTER TABLE `campaign_delivery_count_history`
    ADD CONSTRAINT `fk_campaign_delivery_count_history_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION;


ALTER TABLE `campaign_delivery_count_history`
    ADD CONSTRAINT `fk_campaign_delivery_count_history_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;


ALTER TABLE `campaign` ADD `is_archived` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `status`;
