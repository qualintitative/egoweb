-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 05, 2015 at 02:18 AM
-- Server version: 5.6.21
-- PHP Version: 5.5.18

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `egoweb_restore`
--

-- --------------------------------------------------------

--
-- Table structure for table `alterList`
--

CREATE TABLE IF NOT EXISTS `alterList` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studyId` int(11) NOT NULL,
  `name` text NOT NULL,
  `email` text NOT NULL,
  `ordering` int(11) NOT NULL,
  `interviewerId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=139 ;

-- --------------------------------------------------------

--
-- Table structure for table `alterPrompt`
--

CREATE TABLE IF NOT EXISTS `alterPrompt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studyId` int(11) NOT NULL,
  `afterAltersEntered` int(11) NOT NULL,
  `display` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=211 ;

-- --------------------------------------------------------

--
-- Table structure for table `alters`
--

CREATE TABLE IF NOT EXISTS `alters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(11) DEFAULT NULL,
  `ordering` int(11) NOT NULL,
  `name` text NOT NULL,
  `interviewId` text NOT NULL,
  `alterListId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27291 ;

-- --------------------------------------------------------

--
-- Table structure for table `answer`
--

CREATE TABLE IF NOT EXISTS `answer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `answerType` text,
  PRIMARY KEY (`id`),
  KEY `answerIndex` (`questionId`,`interviewId`,`alterId1`,`alterId2`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1388119 ;

-- --------------------------------------------------------

--
-- Table structure for table `answerList`
--

CREATE TABLE IF NOT EXISTS `answerList` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(11) DEFAULT NULL,
  `listName` text,
  `studyId` int(11) DEFAULT NULL,
  `listOptionNames` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1016 ;

-- --------------------------------------------------------

--
-- Table structure for table `expression`
--

CREATE TABLE IF NOT EXISTS `expression` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(11) DEFAULT '1',
  `name` text,
  `type` text,
  `operator` text,
  `value` text,
  `resultForUnanswered` tinyint(1) DEFAULT NULL,
  `studyId` int(11) DEFAULT NULL,
  `questionId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14916 ;

-- --------------------------------------------------------

--
-- Table structure for table `graphs`
--

CREATE TABLE IF NOT EXISTS `graphs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interviewId` int(11) NOT NULL,
  `expressionId` int(11) NOT NULL,
  `nodes` text CHARACTER SET utf8mb4 NOT NULL,
  `params` text CHARACTER SET utf8mb4 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- Table structure for table `interview`
--

CREATE TABLE IF NOT EXISTS `interview` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) DEFAULT '1',
  `studyId` int(11) DEFAULT NULL,
  `completed` tinyint(1) DEFAULT NULL,
  `start_date` int(11) DEFAULT NULL,
  `complete_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2795 ;

-- --------------------------------------------------------

--
-- Table structure for table `interviewers`
--

CREATE TABLE IF NOT EXISTS `interviewers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studyId` int(11) NOT NULL,
  `interviewerId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `legend`
--

CREATE TABLE IF NOT EXISTS `legend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studyId` int(11) NOT NULL,
  `questionId` int(11) NOT NULL,
  `shape` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interviewId` int(11) NOT NULL,
  `expressionId` int(11) NOT NULL,
  `alterId` varchar(64) DEFAULT NULL,
  `notes` text CHARACTER SET utf32 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE IF NOT EXISTS `question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(11) DEFAULT '1',
  `title` text,
  `prompt` text,
  `preface` text,
  `citation` text,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=38627 ;

-- --------------------------------------------------------

--
-- Table structure for table `questionOption`
--

CREATE TABLE IF NOT EXISTS `questionOption` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(11) DEFAULT '1',
  `studyId` int(11) DEFAULT NULL,
  `questionId` int(11) DEFAULT NULL,
  `name` text,
  `value` text,
  `ordering` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=217770 ;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `id` char(32) NOT NULL,
  `expire` int(11) DEFAULT NULL,
  `data` longblob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `study`
--

CREATE TABLE IF NOT EXISTS `study` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(11) NOT NULL DEFAULT '1',
  `name` text NOT NULL,
  `introduction` text,
  `egoIdPrompt` text,
  `alterPrompt` text,
  `conclusion` text,
  `minAlters` int(11) NOT NULL DEFAULT '0',
  `maxAlters` int(11) NOT NULL DEFAULT '20',
  `adjacencyExpressionId` text,
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
  `completed` int(11) DEFAULT NULL,
  `started` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=279 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `name` text NOT NULL,
  `lastActivity` datetime NOT NULL,
  `permissions` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
