--
-- Update sql for MailWizz EMA from version 2.2.8 to 2.2.9
--

CREATE TABLE IF NOT EXISTS `customer_api_key_permission` (
    `permission_id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `route` varchar(255) NOT NULL,
    `date_added` datetime NOT NULL,
    `last_updated` datetime NOT NULL,
    PRIMARY KEY (`permission_id`),
    UNIQUE KEY `name` (`name`(191)),
    UNIQUE KEY `route` (`route`(191))
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `customer_api_key_to_permission` (
    `key_id` int(11) NOT NULL,
    `permission_id` int(11) NOT NULL,
    PRIMARY KEY (`key_id`, `permission_id`),
    KEY `fk_customer_api_key_to_permission_key1_idx` (`key_id`),
    KEY `fk_customer_api_key_to_permission_permission1_idx` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;

ALTER TABLE `customer_api_key_to_permission`
    ADD CONSTRAINT `fk_customer_api_key_to_permission_key1` FOREIGN KEY (`key_id`) REFERENCES `customer_api_key` (`key_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_customer_api_key_to_permission_permission1` FOREIGN KEY (`permission_id`) REFERENCES `customer_api_key_permission` (`permission_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `customer_api_key` ADD `enable_permissions` enum('no','yes') NOT NULL DEFAULT 'no' AFTER `key`;

INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
('Campaigns - View all', 'campaigns/index', now(), now()),
('Campaigns - View one', 'campaigns/view', now(), now()),
('Campaigns - Create', 'campaigns/create', now(), now()),
('Campaigns - Update', 'campaigns/update', now(), now()),
('Campaigns - Copy', 'campaigns/copy', now(), now()),
('Campaigns - Pause/Unpause', 'campaigns/pause_unpause', now(), now()),
('Campaigns - Mark as sent', 'campaigns/mark_sent', now(), now()),
('Campaigns - Delete', 'campaigns/delete', now(), now()),
('Campaigns - View stats', 'campaigns/stats', now(), now()),
('Campaign Bounces - View logs', 'campaign_bounces/index', now(), now()),
('Campaign Bounces - Create logs', 'campaign_bounces/create', now(), now()),
('Campaign Delivery Logs - View', 'campaign_delivery_logs/index', now(), now()),
('Campaign Delivery Logs - View by email message id', 'campaign_delivery_logs/email_message_id', now(), now()),
('Campaign Unsubscribes - View', 'campaign_unsubscribes/index', now(), now()),
('Campaigns Tracking - Track url', 'campaigns_tracking/track_url', now(), now()),
('Campaigns Tracking - Track open', 'campaigns_tracking/track_open', now(), now()),
('Campaigns Tracking - Track unsubscribe', 'campaigns_tracking/track_unsubscribe', now(), now()),
('Countries - View all', 'countries/index', now(), now()),
('Country - View zones', 'countries/zones', now(), now()),
('Delivery Servers - View all', 'delivery_servers/index', now(), now());

INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
('Delivery Servers - View one', 'delivery_servers/view', now(), now()),
('List Fields - View all', 'list_fields/index', now(), now()),
('List Segments - View all', 'list_segments/index', now(), now()),
('List Subscribers - View all', 'list_subscribers/index', now(), now()),
('List Subscribers - View one', 'list_subscribers/view', now(), now()),
('List Subscribers - Create', 'list_subscribers/create', now(), now()),
('List Subscribers - Create bulk', 'list_subscribers/create_bulk', now(), now()),
('List Subscribers - Update', 'list_subscribers/update', now(), now()),
('List Subscribers - Unsubscribe', 'list_subscribers/unsubscribe', now(), now()),
('List Subscribers - Delete', 'list_subscribers/delete', now(), now()),
('List Subscribers - Search by email', 'list_subscribers/search_by_email', now(), now()),
('List Subscribers - Search by email in all lists', 'list_subscribers/search_by_email_in_all_lists', now(), now()),
('List Subscribers - Unsubscribe by email from all lists', 'list_subscribers/unsubscribe_by_email_from_all_lists', now(), now()),
('List Subscribers - Search by custom fields', 'list_subscribers/search_by_custom_fields', now(), now()),
('Lists - View all', 'lists/index', now(), now()),
('Lists - View one', 'lists/view', now(), now()),
('Lists - Create', 'lists/create', now(), now()),
('Lists - Update', 'lists/update', now(), now()),
('Lists - Copy', 'lists/copy', now(), now()),
('Lists - Delete', 'lists/delete', now(), now());

INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
('Templates - View all', 'templates/index', now(), now()),
('Templates - View one', 'templates/view', now(), now()),
('Templates - Create', 'templates/create', now(), now()),
('Templates - Update', 'templates/update', now(), now()),
('Templates - Delete', 'templates/delete', now(), now()),
('Transactional Emails - View all', 'transactional_emails/index', now(), now()),
('Transactional Emails - View one', 'transactional_emails/view', now(), now()),
('Transactional Emails - Create', 'transactional_emails/create', now(), now()),
('Transactional Emails - Delete', 'transactional_emails/delete', now(), now());