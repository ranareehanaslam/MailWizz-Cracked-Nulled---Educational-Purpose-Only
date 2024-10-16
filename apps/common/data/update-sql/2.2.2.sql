--
-- Update sql for MailWizz EMA from version 2.2.1 to 2.2.2
--

ALTER TABLE `translation_source_message` ADD KEY `category_message_idx` (`category`, `message`(191));

