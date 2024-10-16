SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

-- -----------------------------------------------------
-- Table `ai_assistant_topic`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ai_assistant_topic` (
  `topic_id` INT NOT NULL AUTO_INCREMENT,
  `subject` VARCHAR(100) NOT NULL,
  `prompt` TEXT NOT NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`topic_id`)
)ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;

-- -----------------------------------------------------
-- Table `ai_assistant_conversation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ai_assistant_conversation` (
    `conversation_id` BIGINT NOT NULL AUTO_INCREMENT,
    `topic_id` INT NULL,
    `customer_id` INT NULL,
    `user_id` INT NULL,
    `name` VARCHAR(100) NOT NULL,
    `meta_data` LONGBLOB NULL DEFAULT NULL,
    `date_added` DATETIME NOT NULL,
    `last_updated` DATETIME NOT NULL,
    PRIMARY KEY (`conversation_id`),
    INDEX `fk_ai_assistant_conversation_topic_id_idx` (`topic_id` ASC),
    INDEX `fk_ai_assistant_conversation_customer_id_idx` (`customer_id` ASC),
    INDEX `fk_ai_assistant_conversation_user_id_idx` (`user_id` ASC),
    CONSTRAINT `fk_ai_assistant_conversation_topic_id`
        FOREIGN KEY (`topic_id`)
        REFERENCES `ai_assistant_topic` (`topic_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
    CONSTRAINT `fk_ai_assistant_conversation_customer_id`
        FOREIGN KEY (`customer_id`)
        REFERENCES `customer` (`customer_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    CONSTRAINT `fk_ai_assistant_conversation_user_id`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`user_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `ai_assistant_conversation_message`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ai_assistant_conversation_message` (
    `message_id` INT NOT NULL AUTO_INCREMENT,
    `conversation_id` BIGINT NOT NULL,
    `type` CHAR(20) NOT NULL,
    `message` TEXT NOT NULL,
    `date_added` DATETIME NOT NULL,
    `last_updated` DATETIME NOT NULL,
    PRIMARY KEY (`message_id`),
    INDEX `fk_aiacm_conversation_id_idx` (`conversation_id` ASC),
    CONSTRAINT `fk_aiacm_conversation_id`
        FOREIGN KEY (`conversation_id`)
        REFERENCES `ai_assistant_conversation` (`conversation_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
