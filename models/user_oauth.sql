-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Фев 16 2013 г., 14:12
-- Версия сервера: 5.5.29
-- Версия PHP: 5.3.10-1ubuntu3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `muzforge`
--

-- --------------------------------------------------------

--
-- Структура таблицы `user_oauth`
--

CREATE TABLE IF NOT EXISTS `user_oauth` (
  `user_id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `value` varchar(64) NOT NULL,
  `session_data` text,
  PRIMARY KEY (`name`,`value`),
  UNIQUE KEY `unic_user_id_name` (`user_id`,`name`),
  KEY `oauth_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
