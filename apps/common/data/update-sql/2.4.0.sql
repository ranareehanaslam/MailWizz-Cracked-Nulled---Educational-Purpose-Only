--
-- Update sql for MailWizz EMA from version 2.3.9 to 2.4.0
--

ALTER TABLE `delivery_server` ADD `second_quota` INT(11) NOT NULL DEFAULT '0' AFTER `probability`;
ALTER TABLE `delivery_server` ADD `minute_quota` INT(11) NOT NULL DEFAULT '0' AFTER `second_quota`;
