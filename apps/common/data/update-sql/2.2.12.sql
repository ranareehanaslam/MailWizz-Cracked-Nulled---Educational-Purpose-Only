--
-- Update sql for MailWizz EMA from version 2.2.11 to 2.2.12
--

--
-- Table campaign_option
--
ALTER TABLE `campaign_option` ADD `sending_from_processing_counter` INT(11) NOT NULL DEFAULT '0';
