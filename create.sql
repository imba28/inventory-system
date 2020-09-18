
CREATE TABLE `v_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rentDate` datetime DEFAULT NULL,
  `returnDate` datetime DEFAULT NULL,
  `expectedReturnDate` datetime DEFAULT NULL,
  `createDate` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `CUSTOMER` (`customer_id`),
  KEY `PRODUCT` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `v_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `internal_id` varchar(100) DEFAULT NULL,
  `createDate` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `internal_id_UNIQUE` (`internal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `v_inventurproducts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `inventur_id` int(11) NOT NULL,
  `in_stock` enum('0','1') NOT NULL,
  `missing` enum('0','1') NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  `createDate` datetime NOT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `v_inventurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `startDate` datetime DEFAULT NULL,
  `finishDate` datetime DEFAULT NULL,
  `deleted` enum('1','0') NOT NULL DEFAULT '0',
  `createDate` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `v_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('error','info','warning','debug') NOT NULL DEFAULT 'info',
  `message` text,
  `createDate` datetime NOT NULL,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `v_productimages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `src` varchar(500) DEFAULT NULL,
  `deleted` enum('1','0') NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `createDate` datetime NOT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `v_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(500) NOT NULL,
  `invNr` varchar(45) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `note` text,
  `description` text,
  `condition` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `createDate` datetime NOT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `invNr` (`invNr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `v_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `password` varchar(256) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `createDate` datetime NOT NULL,
  `stamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
