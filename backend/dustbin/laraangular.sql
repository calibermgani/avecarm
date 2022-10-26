
-- Database: `laraangular`
--

-- --------------------------------------------------------

--
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

-- --------------------------------------------------------

--
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

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `user_ips`
--

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

-- --------------------------------------------------------

--
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
-- Indexes for table `address_flags`
--
ALTER TABLE `address_flags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_profile` (`user_id`),
  ADD KEY `fk_user_addressflag` (`address_flag_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_roleid` (`role_id`);

--
-- Indexes for table `user_ips`
--
ALTER TABLE `user_ips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_userid` (`user_id`);

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
-- AUTO_INCREMENT for table `address_flags`
--
ALTER TABLE `address_flags`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `user_ips`
--
ALTER TABLE `user_ips`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_login_historys`
--
ALTER TABLE `user_login_historys`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `fk_user_addressflag` FOREIGN KEY (`address_flag_id`) REFERENCES `address_flags` (`id`),
  ADD CONSTRAINT `fk_user_profile` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_roleid` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_ips`
--
ALTER TABLE `user_ips`
  ADD CONSTRAINT `fk_userid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_login_historys`
--
ALTER TABLE `user_login_historys`
  ADD CONSTRAINT `fk_user_history` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

---------------------Gokul----------------------------------------------------------
--------------------Roles Data-----------------------

INSERT INTO `roles` (`id`, `role_name`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Associate / User', 'Active', 1, 1, '2018-09-11 13:13:49', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'TL / Group Coordinator', 'Active', 1, 1, '2018-09-11 13:13:49', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 'AM and Managers', 'Active', 1, 1, '2018-09-11 13:13:49', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'QC User / Auditor', 'Active', 1, 1, '2018-09-11 13:13:49', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'Administrator', 'Active', 1, 1, '2018-09-11 13:13:49', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
COMMIT;


---------------------------------------------------

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
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
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



--
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
  `guarantor` int(4) NOT NULL,
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
  `created_at` datetime NOT NULL
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


---------------------------------------------
---16/10/2018---------Update on Tables



-- --------------------------------------------------------

--
-- Table structure for table `actions`
--

CREATE TABLE `actions` (
  `id` int(20) NOT NULL,
  `claim_id` int(20) NOT NULL,
  `action_type` varchar(20) NOT NULL,
  `action_id` int(20) NOT NULL,
  `assigned_to` bigint(20) NOT NULL,
  `assigned_by` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) NOT NULL,
  `deleted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `claim_infos`
--

CREATE TABLE `claim_infos` (
  `id` int(20) NOT NULL,
  `claim_number` varchar(20) NOT NULL,
  `patient_id` int(20) NOT NULL,
  `primary_ins_id` int(20) NOT NULL,
  `secondary_ins_id` int(20) NOT NULL,
  `tertiary_ins_id` int(20) NOT NULL,
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

-- --------------------------------------------------------

--
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

-- --------------------------------------------------------

--
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

-- --------------------------------------------------------

--
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

-- --------------------------------------------------------

--
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

-- --------------------------------------------------------

--
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
  `mail_zip5` varchar(5) NOT NULL,
  `mail_zip4` varchar(4) NOT NULL,
  `primary_add_1` varchar(100) NOT NULL,
  `primary_add_2` varchar(100) NOT NULL,
  `primary_city` varchar(20) NOT NULL,
  `primary_state` varchar(20) NOT NULL,
  `primary_zip5` varchar(5) NOT NULL,
  `primary_zip4` varchar(4) NOT NULL,
  `practice_db_id` varchar(20) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) NOT NULL,
  `deleted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `id` int(20) NOT NULL,
  `status_code` varchar(20) NOT NULL,
  `parent_status_id` int(20) NOT NULL,
  `description` varchar(100) NOT NULL,
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
-- Indexes for table `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_claim_action` (`claim_id`),
  ADD KEY `fk_module_action` (`action_id`),
  ADD KEY `fk_user_action_create` (`assigned_to`),
  ADD KEY `fk_user_action_update` (`assigned_by`);

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
-- Indexes for table `insurances`
--
ALTER TABLE `insurances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_ins_created` (`created_by`),
  ADD KEY `fk_user_ins_updated` (`updated_by`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_module_module` (`parent_module_id`),
  ADD KEY `fk_user_module_create` (`created_by`),
  ADD KEY `fk_user_module_update` (`updated_by`);

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
-- Indexes for table `patient_details`
--
ALTER TABLE `patient_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_patient_created` (`created_by`),
  ADD KEY `fk_user_patient_updated` (`updated_by`),
  ADD KEY `fk_claim_patient` (`claim_id`);

--
-- Indexes for table `practices`
--
ALTER TABLE `practices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_practice_create` (`created_by`),
  ADD KEY `fk_user_practice_update` (`updated_by`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_claim_claim` (`parent_status_id`),
  ADD KEY `fk_user_claim_create` (`created_by`),
  ADD KEY `fk_user_claim_update` (`updated_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `actions`
--
ALTER TABLE `actions`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `claim_infos`
--
ALTER TABLE `claim_infos`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `insurances`
--
ALTER TABLE `insurances`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_details`
--
ALTER TABLE `patient_details`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `practices`
--
ALTER TABLE `practices`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `actions`
--
ALTER TABLE `actions`
  ADD CONSTRAINT `fk_claim_action` FOREIGN KEY (`claim_id`) REFERENCES `claim_infos` (`id`),
  ADD CONSTRAINT `fk_module_action` FOREIGN KEY (`action_id`) REFERENCES `modules` (`id`),
  ADD CONSTRAINT `fk_user_action_create` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_action_update` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `claim_infos`
--
ALTER TABLE `claim_infos`
  ADD CONSTRAINT `fk_insurance_claim` FOREIGN KEY (`primary_ins_id`) REFERENCES `insurances` (`id`),
  ADD CONSTRAINT `fk_insurance_claim_sec` FOREIGN KEY (`secondary_ins_id`) REFERENCES `insurances` (`id`),
  ADD CONSTRAINT `fk_insurance_claim_ter` FOREIGN KEY (`tertiary_ins_id`) REFERENCES `insurances` (`id`),
  ADD CONSTRAINT `fk_patient_claim` FOREIGN KEY (`patient_id`) REFERENCES `patient_details` (`id`),
  ADD CONSTRAINT `fk_status_claim` FOREIGN KEY (`claim_status`) REFERENCES `status` (`id`),
  ADD CONSTRAINT `fk_status_claim_sub_sts` FOREIGN KEY (`claim_sub_status`) REFERENCES `status` (`id`),
  ADD CONSTRAINT `fk_user_claim_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_claim_updated` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `insurances`
--
ALTER TABLE `insurances`
  ADD CONSTRAINT `fk_user_ins_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_ins_updated` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `fk_module_module` FOREIGN KEY (`parent_module_id`) REFERENCES `modules` (`id`),
  ADD CONSTRAINT `fk_user_module_create` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_module_update` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `fk_claim_notes` FOREIGN KEY (`claim_id`) REFERENCES `claim_infos` (`id`),
  ADD CONSTRAINT `fk_user_notes` FOREIGN KEY (`user`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_notes_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_notes_updated` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `patient_details`
--
ALTER TABLE `patient_details`
  ADD CONSTRAINT `fk_claim_patient` FOREIGN KEY (`claim_id`) REFERENCES `claim_infos` (`id`),
  ADD CONSTRAINT `fk_user_patient_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_patient_updated` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `practices`
--
ALTER TABLE `practices`
  ADD CONSTRAINT `fk_user_practice_create` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_practice_update` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `status`
--
ALTER TABLE `status`
  ADD CONSTRAINT `fk_claim_claim` FOREIGN KEY (`parent_status_id`) REFERENCES `status` (`id`),
  ADD CONSTRAINT `fk_user_claim_create` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_claim_update` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);
COMMIT;


