--
-- Update sql for MailWizz EMA from version 2.4.0 to 2.4.1
--

-- List fields API permissions

DELETE FROM `customer_api_key_permission` WHERE `route` = 'list_fields/view';
INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
    ('List Fields - View one', 'list_fields/view', now(), now());

DELETE FROM `customer_api_key_permission` WHERE `route` = 'list_fields/create';
INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
    ('List Fields - Create', 'list_fields/create', now(), now());

DELETE FROM `customer_api_key_permission` WHERE `route` = 'list_fields/update';
INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
    ('List Fields - Update', 'list_fields/update', now(), now());

DELETE FROM `customer_api_key_permission` WHERE `route` = 'list_fields/delete';
INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
    ('List Fields - Delete', 'list_fields/delete', now(), now());

DELETE FROM `customer_api_key_permission` WHERE `route` = 'list_fields/list_field_types';
INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
    ('List Fields - View list field types', 'list_fields/list_field_types', now(), now());


-- List segments API permissions

DELETE FROM `customer_api_key_permission` WHERE `route` = 'list_segments/view';
INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
    ('List Segments - View one', 'list_segments/view', now(), now());

DELETE FROM `customer_api_key_permission` WHERE `route` = 'list_segments/create';
INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
    ('List Segments - Create', 'list_segments/create', now(), now());

DELETE FROM `customer_api_key_permission` WHERE `route` = 'list_segments/update';
INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
    ('List Segments - Update', 'list_segments/update', now(), now());

DELETE FROM `customer_api_key_permission` WHERE `route` = 'list_segments/delete';
INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
    ('List Segments - Delete', 'list_segments/delete', now(), now());

DELETE FROM `customer_api_key_permission` WHERE `route` = 'list_segments/condition_operators';
INSERT INTO `customer_api_key_permission` (`name`, `route`, `date_added`, `last_updated`) VALUES
    ('List Segments - Condition operators', 'list_segments/condition_operators', now(), now());
