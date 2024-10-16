--
-- Update sql for MailWizz EMA from version 2.3.7 to 2.3.8
--

ALTER TABLE `delivery_server` CHANGE `use_for` `use_for` VARCHAR(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all';

UPDATE `delivery_server` SET `use_for` = 1 WHERE `use_for` = 'all';
UPDATE `delivery_server` SET `use_for` = 2 WHERE `use_for` = 'transactional';
UPDATE `delivery_server` SET `use_for` = 4 WHERE `use_for` = 'campaigns';
UPDATE `delivery_server` SET `use_for` = 8 WHERE `use_for` = 'email-tests';
UPDATE `delivery_server` SET `use_for` = 16 WHERE `use_for` = 'reports';
UPDATE `delivery_server` SET `use_for` = 32 WHERE `use_for` = 'list-emails';
UPDATE `delivery_server` SET `use_for` = 64 WHERE `use_for` = 'invoices';

ALTER TABLE `delivery_server` CHANGE `use_for` `use_for` INT NOT NULL DEFAULT '1';
ALTER TABLE `delivery_server` ADD INDEX `delivery_server_use_for_idx` (`use_for`);

ALTER TABLE `delivery_server` ADD `force_from_name` VARCHAR(50) NOT NULL DEFAULT 'never' AFTER `force_from`;