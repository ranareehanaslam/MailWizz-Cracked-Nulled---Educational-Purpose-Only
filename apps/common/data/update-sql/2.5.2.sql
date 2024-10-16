--
-- Update sql for MailWizz EMA from version 2.5.1 to 2.5.2
--

ALTER TABLE `list` ADD `double_opt_in_confirmation` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `opt_out`;
