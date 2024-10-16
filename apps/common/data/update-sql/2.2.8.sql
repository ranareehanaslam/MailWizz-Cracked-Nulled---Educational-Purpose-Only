--
-- Update sql for MailWizz EMA from version 2.2.7 to 2.2.8
--

ALTER TABLE `campaign_share_code` ADD `allowed_usage` INT(11) NOT NULL DEFAULT '1' AFTER `used`;
ALTER TABLE `campaign_share_code` ADD `usage_count` INT(11) NOT NULL DEFAULT '0' AFTER `allowed_usage`;
