--
-- Update sql for MailWizz EMA from version 2.5.2 to 2.5.3
--

--
-- Table structure for table `announcement`
--

DROP TABLE IF EXISTS `announcement`;
CREATE TABLE IF NOT EXISTS `announcement` (
    `announcement_id` INT NOT NULL AUTO_INCREMENT,
    `remote_id` CHAR(36) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `date_added` DATETIME NOT NULL,
    `last_updated` DATETIME NOT NULL,
    PRIMARY KEY (`announcement_id`),
    UNIQUE KEY `announcement_remote_id` (`remote_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `user_read_announcement`
--

DROP TABLE IF EXISTS `user_read_announcement`;
CREATE TABLE IF NOT EXISTS `user_read_announcement` (
    `user_id` INT(11) NOT NULL,
    `announcement_id` INT(11) NOT NULL,
    `date_added` DATETIME NOT NULL,
    PRIMARY KEY (`user_id`, `announcement_id`),
    KEY `fk_user_read_announcement_user_idx` (`user_id`),
    KEY `fk_user_read_announcement_announcement_idx` (`announcement_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Constraints for table `user_read_announcement`
--
ALTER TABLE `user_read_announcement`
    ADD CONSTRAINT `fk_user_read_announcement_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_user_read_announcement_announcement` FOREIGN KEY (`announcement_id`) REFERENCES `announcement` (`announcement_id`) ON DELETE CASCADE ON UPDATE NO ACTION;
