CREATE TABLE `users` (
  `id` mediumint NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `display_name` varchar(255),
  `title` varchar(255),
  `bio` varchar(255),
  `date_created` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `date_modified` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

CREATE TABLE `links` (
  `id` mediumint NOT NULL AUTO_INCREMENT,
  `user_id` mediumint NOT NULL,
  `social_media` ENUM(
    'facebook',
    'github',
    'lastfm',
    'linkedin',
    'medium-subdomain',
    'medium-username',
    'twitter'
  ),
  `key` varchar(255),
  `date_created` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `date_modified` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);
