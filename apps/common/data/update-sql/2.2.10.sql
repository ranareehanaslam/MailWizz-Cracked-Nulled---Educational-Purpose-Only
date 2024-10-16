--
-- Update sql for MailWizz EMA from version 2.2.9 to 2.2.10
--

DELETE FROM `customer_api_key_permission` WHERE `route` = 'campaigns_tracking/track_open';

INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
('Campaigns Tracking - Track open', 'campaigns_tracking/track_opening', now(), now());

