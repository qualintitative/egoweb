-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 24, 2014 at 03:34 AM
-- Server version: 5.6.15
-- PHP Version: 5.5.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `egowebbl_egoweb`
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
  `interviewerId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=236 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=90 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4742 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=180939 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=531 ;

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
  PRIMARY KEY (`id`),
  KEY `questionId` (`questionId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12286 ;

-- --------------------------------------------------------

--
-- Table structure for table `graphs`
--

CREATE TABLE IF NOT EXISTS `graphs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb4 NOT NULL,
  `interviewId` int(11) NOT NULL,
  `expressionId` int(11) NOT NULL,
  `json` text CHARACTER SET utf8mb4 NOT NULL,
  `nodes` text CHARACTER SET utf8mb4 NOT NULL,
  `params` text CHARACTER SET utf8mb4 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf32 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Table structure for table `interview`
--

CREATE TABLE IF NOT EXISTS `interview` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) DEFAULT '1',
  `studyId` int(11) DEFAULT NULL,
  `completed` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=826 ;

-- --------------------------------------------------------

--
-- Table structure for table `interviewers`
--

CREATE TABLE IF NOT EXISTS `interviewers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studyId` int(11) NOT NULL,
  `interviewerId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=39 ;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interviewId` int(11) NOT NULL,
  `expressionId` int(11) NOT NULL,
  `alterId` int(11) NOT NULL,
  `notes` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf32 AUTO_INCREMENT=24 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29625 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=116612 ;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `id` char(32) NOT NULL,
  `expire` int(11) DEFAULT NULL,
  `data` longblob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `multiSessionEgoId` int(11) DEFAULT '0',
  `useAsAlters` tinyint(1) DEFAULT '0',
  `restrictAlters` tinyint(1) DEFAULT '0',
  `fillAlterList` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=236 ;

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
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=34 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
