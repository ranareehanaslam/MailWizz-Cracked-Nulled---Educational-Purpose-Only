--
-- Update sql for MailWizz EMA from version 2.1.20 to 2.2.0
--

ALTER TABLE `campaign` 
    ADD `send_between_start` CHAR(8) NULL DEFAULT NULL AFTER `finished_at`, 
    ADD `send_between_end` CHAR(8) NULL DEFAULT NULL AFTER `send_between_start`;