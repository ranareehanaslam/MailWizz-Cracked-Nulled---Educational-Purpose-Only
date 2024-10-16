--
-- Update sql for MailWizz EMA from version 2.3.1 to 2.3.2
--

--
-- Table structure for table `survey_category`
--

DROP TABLE IF EXISTS `survey_category`;
CREATE TABLE IF NOT EXISTS `survey_category` (
    `category_id` INT(11) NOT NULL AUTO_INCREMENT,
    `customer_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `date_added` DATETIME NOT NULL,
    `last_updated` DATETIME NOT NULL,
    PRIMARY KEY (`category_id`),
    KEY `fk_survey_category_customer1_idx` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

--
-- Constraints for table `survey_category`
--
ALTER TABLE `survey_category`
    ADD CONSTRAINT `fk_survey_category_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `survey`
--
ALTER TABLE `survey` ADD `category_id` INT(11) NULL DEFAULT NULL AFTER `customer_id`;
ALTER TABLE `survey` ADD KEY `fk_survey_survey_category1_idx` (`category_id`);
ALTER TABLE `survey`
    ADD CONSTRAINT `fk_survey_survey_category1` FOREIGN KEY (`category_id`) REFERENCES `survey_category` (`category_id`) ON DELETE SET NULL ON UPDATE NO ACTION;
