--
-- Update sql for MailWizz EMA from version 2.4.7 to 2.4.8
--

DELETE FROM `customer_api_key_permission` WHERE `route` = 'list_segments/subscribers';
INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
('List Segments - View subscribers', 'list_segments/subscribers', now(), now());