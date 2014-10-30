CREATE TABLE `member` (
  `member_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(24) NOT NULL,
  `password` varchar(64) NOT NULL,
  `salt` varchar(32) NOT NULL,
  PRIMARY KEY (`member_id`)
);

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `member_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `item_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`)
);
