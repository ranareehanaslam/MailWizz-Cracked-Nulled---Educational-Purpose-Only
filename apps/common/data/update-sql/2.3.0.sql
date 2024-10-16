--
-- Update sql for MailWizz EMA from version 2.2.19 to 2.3.0
--

--
-- Table structure for table `landing_page`
--

DROP TABLE IF EXISTS `landing_page`;
CREATE TABLE IF NOT EXISTS `landing_page` (
    `page_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `revision_id` bigint(20) NULL,
    `customer_id` int(11) NOT NULL,
    `slug` VARCHAR(255) NULL,
    `visitors_count` int(11) NOT NULL DEFAULT 0,
    `views_count` int(11) NOT NULL DEFAULT 0,
    `conversions_count` int(11) NOT NULL DEFAULT 0,
    `has_unpublished_changes` enum('yes', 'no') NOT NULL DEFAULT 'yes',
    `status` enum('published', 'unpublished') NOT NULL DEFAULT 'unpublished',
    `date_added` datetime NOT NULL,
    `last_updated` datetime NOT NULL,
    PRIMARY KEY (`page_id`),
    KEY `fk_landing_page_customer1_idx` (`customer_id`),
    KEY `fk_landing_page_lp_revision_idx` (`revision_id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `landing_page_revision`
--

DROP TABLE IF EXISTS `landing_page_revision`;
CREATE TABLE IF NOT EXISTS `landing_page_revision` (
    `revision_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `page_id` bigint(20) NOT NULL,
    `created_from` bigint(20) NULL DEFAULT NULL,
    `page_type` enum('standard', 'ab-test') NOT NULL DEFAULT 'standard',
    `title` VARCHAR(200) NOT NULL,
    `description` VARCHAR(255) NULL,
    `redirect_url` VARCHAR(255) NULL,
    `redirect_status_code` enum('301', '302') NULL,
    `visitors_count` int(11) NOT NULL DEFAULT 0,
    `views_count` int(11) NOT NULL DEFAULT 0,
    `conversions_count` int(11) NOT NULL DEFAULT 0,
    `date_added` datetime NOT NULL,
    `last_updated` datetime NOT NULL,
    PRIMARY KEY (`revision_id`),
    KEY `fk_landing_page_revision_page_idx` (`page_id`),
    KEY `fk_landing_page_revision_created_from_idx` (`created_from`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `landing_page_revision_variant`
--

DROP TABLE IF EXISTS `landing_page_revision_variant`;
CREATE TABLE IF NOT EXISTS `landing_page_revision_variant` (
    `variant_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `revision_id` bigint(20) NOT NULL,
    `created_from` bigint(20) NULL DEFAULT NULL,
    `title` VARCHAR(200) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `weight` int(11) NOT NULL DEFAULT 100,
    `current_champion` enum('yes', 'no') NOT NULL DEFAULT 'no',
    `active` enum('yes', 'no') NOT NULL DEFAULT 'no',
    `visitors_count` int(11) NOT NULL DEFAULT 0,
    `views_count` int(11) NOT NULL DEFAULT 0,
    `conversions_count` int(11) NOT NULL DEFAULT 0,
    `date_added` datetime NOT NULL,
    `last_updated` datetime NOT NULL,
    PRIMARY KEY (`variant_id`),
    KEY `fk_landing_page_revision_variant_lp_revision_idx` (`revision_id`),
    KEY `fk_landing_page_revision_variant_created_from_idx` (`created_from`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `landing_page_template`
--

DROP TABLE IF EXISTS `landing_page_template`;
CREATE TABLE IF NOT EXISTS `landing_page_template` (
    `template_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `builder_id` VARCHAR(50) NULL,
    `is_blank` enum('yes','no') NOT NULL DEFAULT 'no',
    `title` VARCHAR(200) NOT NULL,
    `content` TEXT NOT NULL,
    `screenshot` varchar(255) NOT NULL,
    `date_added` datetime NOT NULL,
    `last_updated` datetime NOT NULL,
    PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `landing_page_track_visit`
--

DROP TABLE IF EXISTS `landing_page_track_visit`;
CREATE TABLE IF NOT EXISTS `landing_page_track_visit` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `page_id` bigint(20) NOT NULL,
    `revision_id` bigint(20) NOT NULL,
    `variant_id` bigint(20) NOT NULL,
    `location_id` bigint(20) NULL,
    `ip_address` varchar(45) NULL,
    `user_agent` varchar(255) NULL,
    `date_added` datetime NOT NULL,
    PRIMARY KEY (`id`),
    KEY `fk_landing_page_track_visit_page1_idx` (`page_id`),
    KEY `fk_landing_page_track_visit_revision1_idx` (`revision_id`),
    KEY `fk_landing_page_track_visit_variant1_idx` (`variant_id`),
    KEY `fk_landing_page_track_visit_ip_location1_idx` (`location_id`),
    KEY `landing_page_track_visit_ip_page_rev_var` (`ip_address`, `page_id`, `revision_id`, `variant_id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `landing_page_url`
--

DROP TABLE IF EXISTS `landing_page_url`;
CREATE TABLE IF NOT EXISTS `landing_page_url` (
    `url_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `page_id` bigint(20) NOT NULL,
    `revision_id` bigint(20) NOT NULL,
    `variant_id` bigint(20) NOT NULL,
    `hash` char(40) NOT NULL,
    `destination` text NOT NULL,
    `date_added` datetime NOT NULL,
    PRIMARY KEY (`url_id`),
    KEY `fk_landing_page_url_page1_idx` (`page_id`),
    KEY `fk_landing_page_url_revision1_idx` (`revision_id`),
    KEY `fk_landing_page_url_variant1_idx` (`variant_id`),
    KEY `landing_page_url_page_rev_var_hash` (`hash`, `page_id`, `revision_id`, `variant_id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `landing_page_track_url`
--

DROP TABLE IF EXISTS `landing_page_track_url`;
CREATE TABLE IF NOT EXISTS `landing_page_track_url` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `url_id` bigint(20) NOT NULL,
    `location_id` bigint(20) NULL,
    `ip_address` varchar(45) NULL,
    `user_agent` varchar(255) NULL,
    `date_added` datetime NOT NULL,
    PRIMARY KEY (`id`),
    KEY `fk_landing_page_track_url_ip_location1_idx` (`location_id`),
    KEY `fk_landing_page_track_url_landing_page_url1_idx` (`url_id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Constraints for table `landing_page`
--
ALTER TABLE `landing_page`
    ADD CONSTRAINT `fk_landing_page_lp_revision` FOREIGN KEY (`revision_id`) REFERENCES `landing_page_revision` (`revision_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_landing_page_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `landing_page_revision`
--
ALTER TABLE `landing_page_revision`
    ADD CONSTRAINT `fk_landing_page_revision_created_from` FOREIGN KEY (`created_from`) REFERENCES `landing_page_revision` (`revision_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_landing_page_revision_landing_page` FOREIGN KEY (`page_id`) REFERENCES `landing_page` (`page_id`) ON DELETE CASCADE ON UPDATE NO ACTION;


--
-- Constraints for table `landing_page_revision_variant`
--
ALTER TABLE `landing_page_revision_variant`
    ADD CONSTRAINT `fk_landing_page_revision_variant_created_from` FOREIGN KEY (`created_from`) REFERENCES `landing_page_revision_variant` (`variant_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_landing_page_revision_variant_lp_revision` FOREIGN KEY (`revision_id`) REFERENCES `landing_page_revision` (`revision_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `landing_page_track_visit`
--
ALTER TABLE `landing_page_track_visit`
    ADD CONSTRAINT `fk_landing_page_track_visit_page1` FOREIGN KEY (`page_id`) REFERENCES `landing_page` (`page_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_landing_page_track_visit_revision1` FOREIGN KEY (`revision_id`) REFERENCES `landing_page_revision` (`revision_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_landing_page_track_visit_variant1` FOREIGN KEY (`variant_id`) REFERENCES `landing_page_revision_variant` (`variant_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_landing_page_track_visit_ip_location1` FOREIGN KEY (`location_id`) REFERENCES `ip_location` (`location_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints for table `landing_page_url`
--
ALTER TABLE `landing_page_url`
    ADD CONSTRAINT `fk_landing_page_url_page1` FOREIGN KEY (`page_id`) REFERENCES `landing_page` (`page_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_landing_page_url_revision1` FOREIGN KEY (`revision_id`) REFERENCES `landing_page_revision` (`revision_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_landing_page_url_variant1` FOREIGN KEY (`variant_id`) REFERENCES `landing_page_revision_variant` (`variant_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `landing_page_track_url`
--
ALTER TABLE `landing_page_track_url`
    ADD CONSTRAINT `fk_landing_page_track_url_landing_page_url1` FOREIGN KEY (`url_id`) REFERENCES `landing_page_url` (`url_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_landing_page_track_url_ip_location1` FOREIGN KEY (`location_id`) REFERENCES `ip_location` (`location_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

INSERT INTO `landing_page_template` (`template_id`, `is_blank`, `title`, `content`, `screenshot`, `date_added`, `last_updated`) VALUES (NULL, 'yes', 'Blank template', '
<div class="row">
    <div class="col-md-12">
        <div class="display">
            <h1 style="text-transform: uppercase;" class="text-center">Beautiful Content. Responsive.</h1>
            <p class="text-center"><i>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</i></p>
        </div>
    </div>
</div>
', '/frontend/assets/files/landing-page-blank-template.png', NOW(), NOW());
