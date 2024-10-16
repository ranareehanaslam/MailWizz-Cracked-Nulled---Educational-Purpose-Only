--
-- Update sql for MailWizz EMA from version 2.3.2 to 2.3.3
--

--
-- Table structure for table `landing_page_domain`
--

DROP TABLE IF EXISTS `landing_page_domain`;
CREATE TABLE IF NOT EXISTS `landing_page_domain` (
    `domain_id` int(11) NOT NULL AUTO_INCREMENT,
    `customer_id` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `scheme` varchar(50) NOT NULL DEFAULT 'http',
    `verified` ENUM('yes','no') NOT NULL DEFAULT 'no',
    `date_added` datetime NOT NULL,
    `last_updated` datetime NOT NULL,
    PRIMARY KEY (`domain_id`),
    KEY `fk_landing_page_domain_customer1_idx` (`customer_id`)
    ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------


--
-- Alter the landing_page table
--
ALTER TABLE `landing_page` ADD `domain_id` INT(11) NULL AFTER `customer_id`;
ALTER TABLE `landing_page` ADD KEY `fk_landing_page_domain1_idx` (`domain_id`);
ALTER TABLE `landing_page` ADD CONSTRAINT `fk_landing_page_domain1` FOREIGN KEY (`domain_id`) REFERENCES `landing_page_domain` (`domain_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints for table `landing_page_domain`
--
ALTER TABLE `landing_page_domain`
    ADD CONSTRAINT `fk_landing_page_domain_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;
