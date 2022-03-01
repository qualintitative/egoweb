SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
  SET time_zone = "+00:00";


  /*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
  /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
  /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
  /*!40101 SET NAMES utf8mb4 */;

  --
  -- Database: `egoweb`
  --

  -- --------------------------------------------------------

  --
  -- Table structure for table `alterList`
  --

  CREATE TABLE IF NOT EXISTS `alterList` (
    `id` int(11) NOT NULL,
    `studyId` int(11) NOT NULL,
    `name` text NOT NULL,
    `email` text NOT NULL,
    `ordering` int(11) NOT NULL,
    `interviewerId` int(11) NOT NULL,
    `nameGenQIds` text
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

  -- --------------------------------------------------------

  --
  -- Table structure for table `alterPrompt`
  --

  CREATE TABLE IF NOT EXISTS `alterPrompt` (
    `id` int(11) NOT NULL,
    `studyId` int(11) NOT NULL,
    `afterAltersEntered` int(11) NOT NULL,
    `display` text NOT NULL,
    `questionId` int(11) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

  -- --------------------------------------------------------

  --
  -- Table structure for table `alters`
  --

  CREATE TABLE IF NOT EXISTS `alters` (
    `id` int(11) NOT NULL,
    `active` int(11) DEFAULT NULL,
    `ordering` varchar(500) DEFAULT NULL,
    `name` text NOT NULL,
    `interviewId` text NOT NULL,
    `alterListId` varchar(500) DEFAULT NULL,
    `nameGenQIds` text
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `answer`
  --

  CREATE TABLE IF NOT EXISTS `answer` (
    `id` int(11) NOT NULL,
    `active` int(11) DEFAULT NULL,
    `questionId` int(11) DEFAULT NULL,
    `interviewId` int(11) DEFAULT NULL,
    `alterId1` int(11) DEFAULT NULL,
    `alterId2` int(11) DEFAULT NULL,
    `value` text,
    `otherSpecifyText` text,
    `skipReason` text,
    `studyId` int(11) DEFAULT NULL,
    `questionType` text,
    `answerType` text
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `answerList`
  --

  CREATE TABLE IF NOT EXISTS `answerList` (
    `id` int(11) NOT NULL,
    `active` int(11) DEFAULT NULL,
    `listName` text,
    `studyId` int(11) DEFAULT NULL,
    `listOptionNames` text
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `expression`
  --

  CREATE TABLE IF NOT EXISTS `expression` (
    `id` int(11) NOT NULL,
    `active` int(11) DEFAULT '1',
    `name` text,
    `type` text,
    `operator` text,
    `value` text,
    `resultForUnanswered` tinyint(1) DEFAULT NULL,
    `studyId` int(11) DEFAULT NULL,
    `questionId` int(11) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `graphs`
  --

  CREATE TABLE IF NOT EXISTS `graphs` (
    `id` int(11) NOT NULL,
    `interviewId` int(11) NOT NULL,
    `expressionId` int(11) NOT NULL,
    `nodes` text CHARACTER SET utf8mb4 NOT NULL,
    `params` text CHARACTER SET utf8mb4 NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `interview`
  --

  CREATE TABLE IF NOT EXISTS `interview` (
    `id` int(11) NOT NULL,
    `active` tinyint(1) DEFAULT '1',
    `studyId` int(11) DEFAULT NULL,
    `completed` int(11) DEFAULT NULL,
    `start_date` int(11) DEFAULT NULL,
    `complete_date` int(11) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `interviewers`
  --

  CREATE TABLE IF NOT EXISTS `interviewers` (
    `id` int(11) NOT NULL,
    `studyId` int(11) NOT NULL,
    `interviewerId` int(11) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `legend`
  --

  CREATE TABLE IF NOT EXISTS `legend` (
    `id` int(11) NOT NULL,
    `studyId` int(11) NOT NULL,
    `questionId` int(11) NOT NULL,
    `shape` varchar(255) NOT NULL,
    `label` varchar(255) DEFAULT NULL,
    `color` varchar(255) DEFAULT NULL,
    `size` int(11) DEFAULT NULL,
    `ordering` int(11) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `matchedAlters`
  --

  CREATE TABLE IF NOT EXISTS `matchedAlters` (
    `id` int(11) NOT NULL,
    `studyId` int(11) DEFAULT NULL,
    `alterId1` int(11) DEFAULT NULL,
    `alterId2` int(11) DEFAULT NULL,
    `matchedName` varchar(255) NOT NULL,
    `interviewId1` int(11) DEFAULT NULL,
    `interviewId2` int(11) DEFAULT NULL,
    `userId` int(11) DEFAULT NULL,
    `notes` varchar(255) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `notes`
  --

  CREATE TABLE IF NOT EXISTS `notes` (
    `id` int(11) NOT NULL,
    `interviewId` int(11) NOT NULL,
    `expressionId` int(11) NOT NULL,
    `alterId` varchar(64) DEFAULT NULL,
    `notes` text CHARACTER SET utf32 NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `otherSpecify`
  --

  CREATE TABLE IF NOT EXISTS `otherSpecify` (
    `id` int(11) NOT NULL,
    `optionId` int(11) DEFAULT NULL,
    `interviewId` int(11) DEFAULT NULL,
    `value` varchar(255) NOT NULL,
    `alterId` int(11) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `question`
  --

  CREATE TABLE IF NOT EXISTS `question` (
    `id` int(11) NOT NULL,
    `active` int(11) DEFAULT '1',
    `title` text,
    `prompt` longtext,
    `preface` longtext,
    `citation` longtext,
    `subjectType` text,
    `answerType` text,
    `askingStyleList` tinyint(1) DEFAULT NULL,
    `ordering` int(11) DEFAULT NULL,
    `otherSpecify` tinyint(1) DEFAULT NULL,
    `noneButton` text,
    `allButton` text,
    `pageLevelDontKnowButton` text,
    `pageLevelRefuseButton` text,
    `dontKnowButton` tinyint(1) DEFAULT NULL,
    `refuseButton` tinyint(1) DEFAULT NULL,
    `allOptionString` text,
    `uselfExpression` text,
    `minLimitType` text,
    `minLiteral` int(11) DEFAULT NULL,
    `minPrevQues` text,
    `maxLimitType` text,
    `maxLiteral` int(11) DEFAULT NULL,
    `maxPrevQues` text,
    `minCheckableBoxes` int(11) DEFAULT NULL,
    `maxCheckableBoxes` int(11) DEFAULT NULL,
    `withListRange` int(11) DEFAULT NULL,
    `listRangeString` text,
    `minListRange` int(11) DEFAULT NULL,
    `maxListRange` int(11) DEFAULT NULL,
    `timeUnits` int(11) DEFAULT NULL,
    `symmetric` int(11) DEFAULT NULL,
    `keepOnSamePage` int(11) DEFAULT NULL,
    `studyId` int(11) DEFAULT NULL,
    `answerReasonExpressionId` int(11) DEFAULT NULL,
    `networkRelationshipExprId` int(11) DEFAULT NULL,
    `networkParams` text,
    `networkNColorQId` int(11) DEFAULT NULL,
    `networkNSizeQId` int(11) DEFAULT NULL,
    `networkEColorQId` int(11) DEFAULT NULL,
    `networkESizeQId` int(11) DEFAULT NULL,
    `useAlterListField` text,
    `javascript` longtext,
    `restrictList` tinyint(1) DEFAULT NULL,
    `autocompleteList` tinyint(1) DEFAULT NULL,
    `prefillList` tinyint(1) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `questionOption`
  --

  CREATE TABLE IF NOT EXISTS `questionOption` (
    `id` int(11) NOT NULL,
    `active` int(11) DEFAULT '1',
    `studyId` int(11) DEFAULT NULL,
    `questionId` int(11) DEFAULT NULL,
    `name` text,
    `value` text,
    `ordering` int(11) DEFAULT NULL,
    `otherSpecify` tinyint(1) DEFAULT NULL,
    `single` tinyint(1) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `server`
  --

  CREATE TABLE IF NOT EXISTS `server` (
    `id` int(11) NOT NULL,
    `userId` int(11) DEFAULT NULL,
    `address` varchar(255) NOT NULL,
    `username` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `session`
  --

  CREATE TABLE IF NOT EXISTS `session` (
    `id` char(32) NOT NULL,
    `expire` int(11) DEFAULT NULL,
    `data` longblob
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `study`
  --

  CREATE TABLE IF NOT EXISTS `study` (
    `id` int(11) NOT NULL,
    `active` int(11) NOT NULL DEFAULT '1',
    `name` text NOT NULL,
    `introduction` longtext,
    `egoIdPrompt` longtext,
    `alterPrompt` longtext,
    `conclusion` longtext,
    `minAlters` int(11) NOT NULL DEFAULT '0',
    `maxAlters` int(11) NOT NULL DEFAULT '20',
    `valueRefusal` int(11) DEFAULT '-1',
    `valueDontKnow` int(11) DEFAULT '-2',
    `valueLogicalSkip` int(11) DEFAULT '-3',
    `valueNotYetAnswered` int(11) NOT NULL DEFAULT '-4',
    `modified` datetime DEFAULT NULL,
    `multiSessionEgoId` int(11) NOT NULL DEFAULT '0',
    `useAsAlters` tinyint(1) NOT NULL DEFAULT '0',
    `restrictAlters` tinyint(1) NOT NULL DEFAULT '0',
    `fillAlterList` tinyint(1) NOT NULL DEFAULT '0',
    `created_date` int(11) DEFAULT NULL,
    `closed_date` int(11) DEFAULT NULL,
    `status` int(11) DEFAULT NULL,
    `userId` int(11) NOT NULL,
    `hideEgoIdPage` tinyint(1) NOT NULL,
    `style` text,
    `javascript` longtext,
    `footer` longtext,
    `header` longtext
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `tbl_migration`
  --

  CREATE TABLE IF NOT EXISTS `tbl_migration` (
    `version` varchar(255) NOT NULL,
    `apply_time` int(11) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `user`
  --

  CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `name` text NOT NULL,
  `permissions` int(11) NOT NULL DEFAULT 1,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `auth_key` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  --
  -- Indexes for dumped tables
  --

  --
  -- Indexes for table `alterList`
  --
  ALTER TABLE `alterList`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `alterPrompt`
  --
  ALTER TABLE `alterPrompt`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `alters`
  --
  ALTER TABLE `alters`
    ADD PRIMARY KEY (`id`),
    ADD KEY `interviewId` (`interviewId`(32));

  --
  -- Indexes for table `answer`
  --
  ALTER TABLE `answer`
    ADD PRIMARY KEY (`id`),
    ADD KEY `answerIndex` (`questionId`,`interviewId`,`alterId1`,`alterId2`),
    ADD KEY `questionType` (`questionType`(32)),
    ADD KEY `studyId` (`studyId`),
    ADD KEY `interviewId` (`interviewId`),
    ADD KEY `answerIndex2` (`interviewId`,`questionType`(32),`answerType`(32));

  --
  -- Indexes for table `answerList`
  --
  ALTER TABLE `answerList`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `expression`
  --
  ALTER TABLE `expression`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `graphs`
  --
  ALTER TABLE `graphs`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `interview`
  --
  ALTER TABLE `interview`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `interviewers`
  --
  ALTER TABLE `interviewers`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `legend`
  --
  ALTER TABLE `legend`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `matchedAlters`
  --
  ALTER TABLE `matchedAlters`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `notes`
  --
  ALTER TABLE `notes`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `otherSpecify`
  --
  ALTER TABLE `otherSpecify`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `question`
  --
  ALTER TABLE `question`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `questionOption`
  --
  ALTER TABLE `questionOption`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `server`
  --
  ALTER TABLE `server`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `session`
  --
  ALTER TABLE `session`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `study`
  --
  ALTER TABLE `study`
    ADD PRIMARY KEY (`id`);

  --
  -- Indexes for table `tbl_migration`
  --
  ALTER TABLE `tbl_migration`
    ADD PRIMARY KEY (`version`);

  --
  -- Indexes for table `user`
  --
  ALTER TABLE `user`
    ADD PRIMARY KEY (`id`);

  --
  -- AUTO_INCREMENT for dumped tables
  --

  --
  -- AUTO_INCREMENT for table `alterList`
  --
  ALTER TABLE `alterList`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `alterPrompt`
  --
  ALTER TABLE `alterPrompt`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `alters`
  --
  ALTER TABLE `alters`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `answer`
  --
  ALTER TABLE `answer`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `answerList`
  --
  ALTER TABLE `answerList`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `expression`
  --
  ALTER TABLE `expression`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `graphs`
  --
  ALTER TABLE `graphs`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `interview`
  --
  ALTER TABLE `interview`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `interviewers`
  --
  ALTER TABLE `interviewers`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `legend`
  --
  ALTER TABLE `legend`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `matchedAlters`
  --
  ALTER TABLE `matchedAlters`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `notes`
  --
  ALTER TABLE `notes`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `otherSpecify`
  --
  ALTER TABLE `otherSpecify`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `question`
  --
  ALTER TABLE `question`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `questionOption`
  --
  ALTER TABLE `questionOption`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `server`
  --
  ALTER TABLE `server`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `study`
  --
  ALTER TABLE `study`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `user`
  --
  ALTER TABLE `user`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

INSERT INTO `tbl_migration` (`version`, `apply_time`) VALUES
('m140917_223213_graph_update', 1453111846),
('m140918_011844_change_alter_id', 1453111846),
('m140924_014007_add_dates_to_interview', 1453111846),
('m140924_052523_add_dates_to_study', 1453111846),
('m141015_042552_legend', 1453111947),
('m141020_221311_encrypt_answer', 1453111947),
('m141023_003707_encrypt_alters', 1453111948),
('m141023_004706_encrypt_alterList', 1453111948),
('m141023_005646_encrypt_notes', 1453111948),
('m141023_010620_encrypt_user', 1453111948),
('m141118_014141_add_completed_started_and_status_to_study', 1453112258),
('m150202_163202_add_userId_study', 1453112258),
('m150227_070331_hide_ego_id', 1453112258),
('m150303_014601_change_completed_type', 1453112258),
('m150314_043742_otherSpecUpdate', 1453112258),
('m150506_232010_matchTable', 1453112258),
('m160118_100231_image_longtext', 1453112259),
('m160201_074143_style_css', 1454312829),
('m160325_091242_js_and_footer', 1458898123),
('m160407_002139_js_question', 1459988879),
('m160919_094624_header', 1474279347),
('m170127_113542_add_matched_interviews', 1485613302),
('m170317_083540_add_alter_matcher', 1489740523),
('m170912_064545_name_generators', 1505200157),
('m171010_091427_add_var_prompt_q_id', 1507650908),
('m180320_124446_add_alterlist_namegen', 1521550344),
('m180413_082300_server_table', 1523728116),
('m181210_202059_question_autofill_restrict', 1550777561),
('m181214_010547_prefill_list', 1550777561),
('m190130_075827_matchnotes', 1550777561),
('m201027_223018_alter_order', 1613374253),
('m201126_180202_refuse_option', 1613374253),
('m210317_002638_modify_alter_match', 1623877304);