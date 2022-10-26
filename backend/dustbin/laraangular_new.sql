
-- Database: `laraangular`
--

-- --------------------------------------------------------
---- Table structure for table `actions`
--

CREATE TABLE `actions` (
  `id` int(20) NOT NULL,
  `claim_id` int(20) DEFAULT NULL,
  `action_type` varchar(20) NOT NULL,
  `action_id` int(20) DEFAULT NULL,
  `assigned_to` bigint(20) NOT NULL,
  `assigned_by` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_claim_action` (`claim_id`),
  ADD KEY `fk_module_action` (`action_id`),
  ADD KEY `fk_user_action_create` (`assigned_to`),
  ADD KEY `fk_user_action_update` (`assigned_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `actions`
--
ALTER TABLE `actions`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `actions`
--
ALTER TABLE `actions`
  ADD CONSTRAINT `fk_module_action` FOREIGN KEY (`action_id`) REFERENCES `modules` (`id`),
  ADD CONSTRAINT `fk_user_action_create` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_action_update` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);
COMMIT;


-----------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `address_flags`
--

CREATE TABLE `address_flags` (
  `id` int(10) NOT NULL,
  `address_company` enum('usps') NOT NULL,
  `type` enum('patients','users') NOT NULL,
  `address_line_1` varchar(25) NOT NULL,
  `address_line_2` varchar(25) NOT NULL,
  `city` varchar(25) NOT NULL,
  `state` varchar(20) NOT NULL,
  `zip5` int(6) NOT NULL,
  `zip4` int(4) NOT NULL,
  `is_address_match` enum('Yes','No') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address_flags`
--
ALTER TABLE `address_flags`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `address_flags`
--
ALTER TABLE `address_flags`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;

----------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `claim_histories`
--

CREATE TABLE `claim_histories` (
  `id` int(20) NOT NULL,
  `claim_id` varchar(20) NOT NULL,
  `claim_state` varchar(30) NOT NULL,
  `assigned_by` int(20) DEFAULT NULL,
  `assigned_to` int(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `claim_histories`
--
ALTER TABLE `claim_histories`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `claim_histories`
--
ALTER TABLE `claim_histories`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;
COMMIT;
-----------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `claim_infos`
--

CREATE TABLE `claim_infos` (
  `id` int(20) NOT NULL,
  `claim_number` varchar(20) NOT NULL,
  `patient_id` int(20) DEFAULT NULL,
  `primary_ins_id` int(20) DEFAULT NULL,
  `secondary_ins_id` int(20) DEFAULT NULL,
  `tertiary_ins_id` int(20) DEFAULT NULL,
  `rendering_provider` varchar(50) NOT NULL,
  `billing_provider` varchar(50) NOT NULL,
  `facility` varchar(50) NOT NULL,
  `dos_from` datetime NOT NULL,
  `dos_to` datetime NOT NULL,
  `admit_date` datetime NOT NULL,
  `discharge_date` datetime NOT NULL,
  `cpt` varchar(10) NOT NULL,
  `icd` varchar(100) NOT NULL,
  `modifier` varchar(100) NOT NULL,
  `units` double NOT NULL,
  `total_charges` double NOT NULL,
  `pat_ar` double NOT NULL,
  `ins_ar` double NOT NULL,
  `total_ar_due` double NOT NULL,
  `claim_status` int(20) NOT NULL,
  `claim_sub_status` int(20) NOT NULL,
  `responsibility` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) NOT NULL,
  `deleted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `claim_infos`
--
ALTER TABLE `claim_infos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_patient_claim` (`patient_id`),
  ADD KEY `fk_insurance_claim` (`primary_ins_id`),
  ADD KEY `fk_insurance_claim_sec` (`secondary_ins_id`),
  ADD KEY `fk_insurance_claim_ter` (`tertiary_ins_id`),
  ADD KEY `fk_user_claim_created` (`created_by`),
  ADD KEY `fk_user_claim_updated` (`updated_by`),
  ADD KEY `fk_status_claim` (`claim_status`),
  ADD KEY `fk_status_claim_sub_sts` (`claim_sub_status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `claim_infos`
--
ALTER TABLE `claim_infos`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claim_infos`
--
ALTER TABLE `claim_infos`
  ADD CONSTRAINT `fk_insurance_claim` FOREIGN KEY (`primary_ins_id`) REFERENCES `insurances` (`id`),
  ADD CONSTRAINT `fk_insurance_claim_sec` FOREIGN KEY (`secondary_ins_id`) REFERENCES `insurances` (`id`),
  ADD CONSTRAINT `fk_insurance_claim_ter` FOREIGN KEY (`tertiary_ins_id`) REFERENCES `insurances` (`id`),
  ADD CONSTRAINT `fk_patient_claim` FOREIGN KEY (`patient_id`) REFERENCES `patient_details` (`id`),
  ADD CONSTRAINT `fk_status_claim` FOREIGN KEY (`claim_status`) REFERENCES `sub_status` (`id`),
  ADD CONSTRAINT `fk_status_claim_sub_sts` FOREIGN KEY (`claim_sub_status`) REFERENCES `sub_status` (`id`),
  ADD CONSTRAINT `fk_user_claim_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_claim_updated` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);
COMMIT;

------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `claim_notes`
--

CREATE TABLE `claim_notes` (
  `id` int(10) NOT NULL,
  `claim_id` bigint(20) NOT NULL,
  `state` enum('Active','Inactive','Edited') NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `claim_notes`
--
ALTER TABLE `claim_notes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `claim_notes`
--
ALTER TABLE `claim_notes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;

---------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `claim_states`
--

CREATE TABLE `claim_states` (
  `id` int(20) NOT NULL,
  `name` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `claim_states`
--
ALTER TABLE `claim_states`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `claim_states`
--
ALTER TABLE `claim_states`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;
COMMIT;

-----------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `file_uploads`
--

CREATE TABLE `file_uploads` (
  `id` int(10) NOT NULL,
  `report_date` datetime NOT NULL,
  `file_name` varchar(100) NOT NULL,
  `file_url` varchar(200) NOT NULL,
  `notes` varchar(500) NOT NULL,
  `total_claims` int(5) NOT NULL,
  `new_claims` int(5) NOT NULL,
  `Import_by` bigint(20) NOT NULL,
  `claims_processed` int(4) NOT NULL,
  `status` enum('Complete','Incomplete') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `file_uploads`
--
ALTER TABLE `file_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_fileupld` (`Import_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `file_uploads`
--
ALTER TABLE `file_uploads`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `file_uploads`
--
ALTER TABLE `file_uploads`
  ADD CONSTRAINT `fk_user_fileupld` FOREIGN KEY (`Import_by`) REFERENCES `users` (`id`);
COMMIT;

-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `followup_categories`
--

CREATE TABLE `followup_categories` (
  `id` int(10) NOT NULL,
  `name` varchar(120) NOT NULL,
  `label_name` varchar(120) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(20) NOT NULL,
  `updated_by` int(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` int(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `followup_categories`
--

INSERT INTO `followup_categories` (`id`, `name`, `label_name`, `status`, `created_at`, `created_by`, `updated_by`, `updated_at`, `deleted_at`, `deleted_by`) VALUES
(1, 'Casdagorty 1', 'Cat1', 'Active', '2019-01-02 08:33:02', 21, 21, '2018-11-20 04:57:56', NULL, NULL),
(2, 'Categorty 2', 'Cat2', 'Active', '2018-11-21 10:45:51', 21, NULL, '2018-11-20 04:58:14', NULL, NULL),
(3, 'Categorty 3', 'Cat3', 'Active', '2018-11-21 10:45:57', 21, NULL, '2018-11-20 06:22:28', NULL, NULL),
(4, 'Category4', 'CAT4', 'Active', '2018-11-22 10:17:16', 21, 21, '2018-11-21 05:36:38', NULL, NULL),
(5, 'category5', 'cat5', 'Active', '2018-11-21 06:27:44', 21, NULL, '2018-11-21 06:27:44', NULL, NULL),
(6, 'Cat6', 'cat6asd', 'Active', '2018-11-22 07:52:43', 21, 21, '2018-11-21 06:46:15', NULL, NULL),
(7, 'Cat7', 'cat7', 'Active', '2018-11-22 04:59:45', 21, 21, '2018-11-21 06:47:48', NULL, NULL),
(8, 'Cat8', 'Categoptr8', 'Active', '2018-12-28 01:52:46', 21, NULL, '2018-12-28 01:52:46', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `followup_categories`
--
ALTER TABLE `followup_categories`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `followup_categories`
--
ALTER TABLE `followup_categories`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

--------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `followup_questions`
--

CREATE TABLE `followup_questions` (
  `id` int(10) NOT NULL,
  `question` text NOT NULL,
  `question_label` text NOT NULL,
  `hint` varchar(120) NOT NULL,
  `category_id` int(11) NOT NULL,
  `field_type` enum('Date','Number','Text') NOT NULL,
  `field_validation` enum('Number','Text','Both') DEFAULT NULL,
  `date_type` enum('single_date','double_date') DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `followup_questions`
--
ALTER TABLE `followup_questions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `followup_questions`
--
ALTER TABLE `followup_questions`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;

-------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `followup_templates`
--

CREATE TABLE `followup_templates` (
  `id` int(20) NOT NULL,
  `claim_id` int(20) NOT NULL,
  `rep_name` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `phone` varchar(15) NOT NULL,
  `insurance_id` int(20) NOT NULL,
  `category_id` int(20) NOT NULL,
  `content` text NOT NULL,
  `created_by` int(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `followup_templates`
--
ALTER TABLE `followup_templates`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `followup_templates`
--
ALTER TABLE `followup_templates`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;
COMMIT;

-----------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `import_fields`
--

CREATE TABLE `import_fields` (
  `id` int(10) NOT NULL,
  `file_upload_id` int(10) NOT NULL,
  `acct_no` varchar(20) NOT NULL,
  `claim_no` varchar(100) NOT NULL,
  `patient_name` varchar(200) NOT NULL,
  `dos` varchar(20) NOT NULL,
  `dob` varchar(20) NOT NULL,
  `ssn` varchar(20) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `phone_no` varchar(20) NOT NULL,
  `address_1` varchar(100) NOT NULL,
  `address_2` varchar(100) NOT NULL,
  `city` varchar(30) NOT NULL,
  `state` varchar(20) NOT NULL,
  `zipcode` varchar(10) NOT NULL,
  `guarantor` varchar(20) NOT NULL,
  `employer` varchar(100) NOT NULL,
  `responsibility` varchar(100) NOT NULL,
  `insurance_type` varchar(100) NOT NULL,
  `prim_ins_name` varchar(100) NOT NULL,
  `prim_pol_id` varchar(50) NOT NULL,
  `prim_group_id` varchar(50) NOT NULL,
  `prim_address_1` varchar(100) NOT NULL,
  `prim_address_2` varchar(100) NOT NULL,
  `prim_city` varchar(30) NOT NULL,
  `prim_state` varchar(20) NOT NULL,
  `prim_zipcode` varchar(10) NOT NULL,
  `sec_ins_name` varchar(50) NOT NULL,
  `sec_pol_id` varchar(50) NOT NULL,
  `sec_group_id` varchar(50) NOT NULL,
  `sec_address_1` varchar(100) NOT NULL,
  `sec_address_2` varchar(100) NOT NULL,
  `sec_city` varchar(30) NOT NULL,
  `sec_state` varchar(20) NOT NULL,
  `sec_zipcode` varchar(10) NOT NULL,
  `ter_ins_name` varchar(50) NOT NULL,
  `ter_pol_id` varchar(50) NOT NULL,
  `ter_group_id` varchar(50) NOT NULL,
  `ter_address_1` varchar(100) NOT NULL,
  `ter_address_2` varchar(100) NOT NULL,
  `ter_city` varchar(30) NOT NULL,
  `ter_state` varchar(20) NOT NULL,
  `ter_zipcode` varchar(10) NOT NULL,
  `auth_no` varchar(50) NOT NULL,
  `rendering_prov` varchar(50) NOT NULL,
  `billing_prov` varchar(50) NOT NULL,
  `facility` varchar(100) NOT NULL,
  `admit_date` varchar(20) NOT NULL,
  `discharge_date` varchar(20) NOT NULL,
  `cpt` varchar(20) NOT NULL,
  `icd` varchar(10) NOT NULL,
  `modifiers` varchar(50) NOT NULL,
  `units` varchar(10) NOT NULL,
  `total_charges` varchar(20) NOT NULL,
  `pat_ar` varchar(20) NOT NULL,
  `ins_ar` varchar(20) NOT NULL,
  `total_ar` varchar(20) NOT NULL,
  `claim_Status` varchar(20) NOT NULL,
  `claim_note` varchar(500) NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `assigned_to` int(20) DEFAULT NULL,
  `status_code` int(20) DEFAULT NULL,
  `substatus_code` int(20) DEFAULT NULL,
  `followup_associate` int(20) DEFAULT NULL,
  `followup_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `import_fields`
--
ALTER TABLE `import_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_upload_id` (`file_upload_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `import_fields`
--
ALTER TABLE `import_fields`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `import_fields`
--
ALTER TABLE `import_fields`
  ADD CONSTRAINT `fk_upload_id` FOREIGN KEY (`file_upload_id`) REFERENCES `file_uploads` (`id`);
COMMIT;

-------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `insurances`
--

CREATE TABLE `insurances` (
  `id` int(20) NOT NULL,
  `ins_name` varchar(100) NOT NULL,
  `ins_type` enum('Primary','Secondary','Tertiary','Others') NOT NULL,
  `policy_id` varchar(20) NOT NULL,
  `group_id` varchar(20) NOT NULL,
  `ins_address_line_1` varchar(100) NOT NULL,
  `ins_address_line_2` varchar(100) NOT NULL,
  `ins_city` varchar(20) NOT NULL,
  `ins_state` varchar(20) NOT NULL,
  `ins_zipcode` varchar(10) NOT NULL,
  `ins_phone_no` int(15) NOT NULL,
  `ins_auth` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) NOT NULL,
  `deleted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `insurances`
--
ALTER TABLE `insurances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_ins_created` (`created_by`),
  ADD KEY `fk_user_ins_updated` (`updated_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `insurances`
--
ALTER TABLE `insurances`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `insurances`
--
ALTER TABLE `insurances`
  ADD CONSTRAINT `fk_user_ins_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_ins_updated` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);
COMMIT;

------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(20) NOT NULL,
  `module_name` varchar(20) NOT NULL,
  `parent_module_id` int(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) NOT NULL,
  `deleted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_module_module` (`parent_module_id`),
  ADD KEY `fk_user_module_create` (`created_by`),
  ADD KEY `fk_user_module_update` (`updated_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `fk_module_module` FOREIGN KEY (`parent_module_id`) REFERENCES `modules` (`id`),
  ADD CONSTRAINT `fk_user_module_create` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_module_update` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);
COMMIT;

-------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(20) NOT NULL,
  `claim_id` int(20) NOT NULL,
  `notes` varchar(100) NOT NULL,
  `notes_type` enum('Claim','Action','Process','Followup') NOT NULL,
  `user` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) NOT NULL,
  `deleted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_claim_notes` (`claim_id`),
  ADD KEY `fk_user_notes` (`user`),
  ADD KEY `fk_user_notes_created` (`created_by`),
  ADD KEY `fk_user_notes_updated` (`updated_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `fk_claim_notes` FOREIGN KEY (`claim_id`) REFERENCES `claim_infos` (`id`),
  ADD CONSTRAINT `fk_user_notes` FOREIGN KEY (`user`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_notes_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_notes_updated` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);
COMMIT;

-----------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `patient_details`
--

CREATE TABLE `patient_details` (
  `id` int(20) NOT NULL,
  `acct_no` varchar(20) NOT NULL,
  `claim_id` int(20) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `dob` datetime NOT NULL,
  `ssn` varchar(20) NOT NULL,
  `gender` enum('Male','Female','Others') NOT NULL,
  `phone_no` varchar(15) NOT NULL,
  `address_line_1` varchar(100) NOT NULL,
  `address_line_2` varchar(100) NOT NULL,
  `city` varchar(20) NOT NULL,
  `state` varchar(20) NOT NULL,
  `zipcode` varchar(10) NOT NULL,
  `gurantor_name` varchar(100) NOT NULL,
  `employer_name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) NOT NULL,
  `deleted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `patient_details`
--
ALTER TABLE `patient_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_patient_created` (`created_by`),
  ADD KEY `fk_user_patient_updated` (`updated_by`),
  ADD KEY `fk_claim_patient` (`claim_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `patient_details`
--
ALTER TABLE `patient_details`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `patient_details`
--
ALTER TABLE `patient_details`
  ADD CONSTRAINT `fk_claim_patient` FOREIGN KEY (`claim_id`) REFERENCES `claim_infos` (`id`),
  ADD CONSTRAINT `fk_user_patient_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_patient_updated` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);
COMMIT;

-----------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `practices`
--

CREATE TABLE `practices` (
  `id` int(20) NOT NULL,
  `practice_name` varchar(20) NOT NULL,
  `practice_description` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `fax` varchar(15) NOT NULL,
  `avatar_name` varchar(100) NOT NULL,
  `practice_link` varchar(100) NOT NULL,
  `doing_business_as` varchar(100) NOT NULL,
  `speciality_id` varchar(20) NOT NULL,
  `taxanomy_id` varchar(20) NOT NULL,
  `billing_entity` enum('Yes','No') NOT NULL,
  `entity_type` enum('Individual','Group') NOT NULL,
  `tax_id` varchar(20) NOT NULL,
  `group_tax_id` varchar(20) NOT NULL,
  `npi` varchar(20) NOT NULL,
  `group_npi` varchar(20) NOT NULL,
  `medicare_ptan` varchar(20) NOT NULL,
  `medicaid` varchar(20) NOT NULL,
  `mail_add_1` varchar(100) NOT NULL,
  `mail_add_2` varchar(100) NOT NULL,
  `mail_city` varchar(20) NOT NULL,
  `mail_state` varchar(20) NOT NULL,
  `mail_zip5` varchar(10) NOT NULL,
  `mail_zip4` varchar(10) NOT NULL,
  `primary_add_1` varchar(100) NOT NULL,
  `primary_add_2` varchar(100) NOT NULL,
  `primary_city` varchar(20) NOT NULL,
  `primary_state` varchar(20) NOT NULL,
  `primary_zip5` varchar(10) NOT NULL,
  `primary_zip4` varchar(10) NOT NULL,
  `practice_db_id` varchar(20) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `practices`
--
ALTER TABLE `practices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_practice_create` (`created_by`),
  ADD KEY `fk_user_practice_update` (`updated_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `practices`
--
ALTER TABLE `practices`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `practices`
--
ALTER TABLE `practices`
  ADD CONSTRAINT `fk_user_practice_create` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_practice_update` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);
COMMIT;

----------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `process_notes`
--

CREATE TABLE `process_notes` (
  `id` int(10) NOT NULL,
  `claim_id` bigint(20) NOT NULL,
  `state` enum('Active','Inactive','Edited') NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `process_notes`
--
ALTER TABLE `process_notes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `process_notes`
--
ALTER TABLE `process_notes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;

-------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `employee_code` varchar(10) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female','Others') NOT NULL,
  `mobile_phone` varchar(15) NOT NULL,
  `work_phone` varchar(15) NOT NULL,
  `address_flag_id` int(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_profile` (`user_id`),
  ADD KEY `fk_user_addressflag` (`address_flag_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `fk_user_addressflag` FOREIGN KEY (`address_flag_id`) REFERENCES `address_flags` (`id`),
  ADD CONSTRAINT `fk_user_profile` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `qc_notes`
--

CREATE TABLE `qc_notes` (
  `id` int(10) NOT NULL,
  `claim_id` bigint(20) NOT NULL,
  `state` enum('Active','Inactive','Edited') NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `qc_notes`
--
ALTER TABLE `qc_notes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `qc_notes`
--
ALTER TABLE `qc_notes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;

----------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `roles`

CREATE TABLE `roles` (
  `id` int(10) NOT NULL,
  `role_name` varchar(25) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;

----------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `status`

CREATE TABLE `status` (
  `id` int(20) NOT NULL,
  `status_code` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `status` int(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;
COMMIT;

-------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `sub_status`

CREATE TABLE `sub_status` (
  `id` int(20) NOT NULL,
  `status_code` varchar(20) NOT NULL,
  `parent_status_id` int(20) DEFAULT NULL,
  `description` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `status` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sub_status`
--
ALTER TABLE `sub_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_claim_create` (`created_by`),
  ADD KEY `fk_user_claim_update` (`updated_by`),
  ADD KEY `fk_substatus_status` (`parent_status_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sub_status`
--
ALTER TABLE `sub_status`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sub_status`
--
ALTER TABLE `sub_status`
  ADD CONSTRAINT `fk_substatus_status` FOREIGN KEY (`parent_status_id`) REFERENCES `status` (`id`),
  ADD CONSTRAINT `fk_user_claim_create` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_claim_update` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);
COMMIT;

------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `test`

CREATE TABLE `test` (
  `id` int(20) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Points` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `test`
--
ALTER TABLE `test`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;
COMMIT;

----------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `users`

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `role_id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(60) NOT NULL,
  `user_type` enum('Practice','Medcubics') NOT NULL,
  `last_login` datetime NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL,
  `token` varchar(250) NOT NULL,
  `is_logged_in` enum('0','1') NOT NULL,
  `login_attempt` int(10) NOT NULL,
  `attempt_updated` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_roleid` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_roleid` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

-------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `user_ips`

CREATE TABLE `user_ips` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `ip_address` varchar(20) NOT NULL,
  `approved` enum('No','Yes') NOT NULL,
  `security_code` int(11) NOT NULL,
  `security_code_attempt` int(11) NOT NULL,
  `first_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user_ips`
--
ALTER TABLE `user_ips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_userid` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user_ips`
--
ALTER TABLE `user_ips`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_ips`
--
ALTER TABLE `user_ips`
  ADD CONSTRAINT `fk_userid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

--------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `user_login_historys`
--

CREATE TABLE `user_login_historys` (
  `id` int(10) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(25) NOT NULL,
  `login_time` varchar(25) NOT NULL,
  `logout_time` varchar(25) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user_login_historys`
--
ALTER TABLE `user_login_historys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_history` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user_login_historys`
--
ALTER TABLE `user_login_historys`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_login_historys`
--
ALTER TABLE `user_login_historys`
  ADD CONSTRAINT `fk_user_history` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

---------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `workorder_fields`
--

CREATE TABLE `workorder_fields` (
  `id` int(20) NOT NULL,
  `work_order_name` varchar(30) NOT NULL,
  `due_date` date NOT NULL,
  `status` varchar(20) NOT NULL,
  `priority` varchar(20) NOT NULL,
  `work_notes` text NOT NULL,
  `created_by` int(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` date DEFAULT NULL,
  `deleted_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `workorder_fields`
--
ALTER TABLE `workorder_fields`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `workorder_fields`
--
ALTER TABLE `workorder_fields`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;
COMMIT;

-----------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `workorder_user_fields`
--

CREATE TABLE `workorder_user_fields` (
  `id` int(20) NOT NULL,
  `work_order_id` int(20) NOT NULL,
  `user_id` int(20) NOT NULL,
  `cliam_no` text NOT NULL,
  `completed_claim` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `workorder_user_fields`
--
ALTER TABLE `workorder_user_fields`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `workorder_user_fields`
--
ALTER TABLE `workorder_user_fields`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;
COMMIT;

-----------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `client_notes`
--


CREATE TABLE `client_notes` (
  `id` int(10) NOT NULL,
  `claim_id` varchar(20) NOT NULL,
  `state` enum('Active','Inactive','Edited') NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `client_notes`
--
ALTER TABLE `client_notes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `client_notes`
--
ALTER TABLE `client_notes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;

-----------------------------------------------------------------------------------------------------------------------------------------------------------
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(10) NOT NULL,
  `document_name` varchar(20) DEFAULT NULL,
  `category` varchar(30) NOT NULL,
  `file_name` text,
  `uploaded_name` text,
  `archived` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(20) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;