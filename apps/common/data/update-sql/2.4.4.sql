--
-- Update sql for MailWizz EMA from version 2.4.3 to 2.4.4
--

ALTER TABLE `survey` ADD `max_responder_responses` int(11) NOT NULL DEFAULT '-1' AFTER `finish_redirect`;
