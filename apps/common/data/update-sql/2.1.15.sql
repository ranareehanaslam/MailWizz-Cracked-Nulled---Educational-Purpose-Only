--
-- Update sql for MailWizz EMA from version 2.1.14 to 2.1.15
--

ALTER TABLE `campaign_option`
    ADD `open_tracking_from_url_tracking` ENUM('yes','no') NOT NULL DEFAULT 'yes' AFTER `url_tracking`,
    ADD `open_tracking_exclude_crawlers` ENUM('no','yes') NOT NULL DEFAULT 'no' AFTER `open_tracking_from_url_tracking`, 
    ADD `url_tracking_exclude_crawlers` ENUM('no','yes') NOT NULL DEFAULT 'no' AFTER `open_tracking_exclude_crawlers`;