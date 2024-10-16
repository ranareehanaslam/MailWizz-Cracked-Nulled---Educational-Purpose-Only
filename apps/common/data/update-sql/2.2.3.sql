--
-- Update sql for MailWizz EMA from version 2.2.2 to 2.2.3
--

UPDATE `list_page_type` SET `description`='The email the user receives after they successfully subscribe into the list' WHERE `description`='The email the user receives after he successfully subscribes into the list';
UPDATE `list_page_type` SET `description`='The email the user receives after their subscription is approved.' WHERE `description`='The email the user receives after his subscription is approved.';

DELETE FROM `translation_source_message` WHERE `category`='campaigns' AND `message`='What action to take against the subscriber when he opens the campaign';
DELETE FROM `translation_source_message` WHERE `category`='settings' AND `message`='If waiting is enabled and the customer sends all emails in an hour, he will wait 23 more hours until the specified action is taken.';
DELETE FROM `translation_source_message` WHERE `category`='lists' AND `message`='Optionally, a url to redirect the visitor if the subscriber hasn''t been found in the list or he isn''t valid anymore.';
DELETE FROM `translation_source_message` WHERE `category`='lists' AND `message`='When a subscriber will subscribe into this list, if he exists in any of the lists below, unsubscribe him from them. Please note that the unsubscribe from the lists below is silent, no email is sent to the subscriber.';
DELETE FROM `translation_source_message` WHERE `category`='lists' AND `message`='When a subscriber will unsubscribe from this list, if he exists in any of the lists below, unsubscribe him from them too. Please note that the unsubscribe from the lists below is silent, no email is sent to the subscriber.';
DELETE FROM `translation_source_message` WHERE `category`='servers' AND `message`='Login as this customer and edit the monitor from his account if you want the conditions saved!';
DELETE FROM `translation_source_message` WHERE `category`='settings' AND `message`='If the customer disables his account, how many days we should keep it in the system until we remove it for good. Set to -1 for unlimited';
DELETE FROM `translation_source_message` WHERE `category`='settings' AND `message`='Whether missing subscriber optin history can be updated when the subscriber will update his profile';

--
-- Table structure for table `list_subscriber_meta_data`
--

DROP TABLE IF EXISTS `list_subscriber_meta_data`;
CREATE TABLE IF NOT EXISTS `list_subscriber_meta_data` (
  `subscriber_id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` BLOB,
  `is_serialized` TINYINT(1) NOT NULL DEFAULT 0,
  `is_private` TINYINT(1) NOT NULL DEFAULT 1,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`subscriber_id`, `key`(188)),
  KEY `fk_list_subscriber_meta_data_subscriber1_idx` (`subscriber_id`),
  KEY `list_subscriber_meta_data_key1` (`key`(191))
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

--
-- Constraints for table `list_subscriber_meta_data`
--
ALTER TABLE `list_subscriber_meta_data`
    ADD CONSTRAINT `fk_list_subscriber_meta_data_subscriber1_idx` FOREIGN KEY (`subscriber_id`) REFERENCES `list_subscriber` (`subscriber_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Alter the campaigns table
--
UPDATE `campaign` SET `send_between_start` = NULL, `send_between_end` = NULL WHERE 1;
ALTER TABLE `campaign` CHANGE `send_between_end` `send_between_interval` INT(2) NULL DEFAULT NULL;
