LOCK TABLES `users` WRITE;
INSERT INTO `users` VALUES
    (1, 'phil.laf@gmail.com', 'phil', '$2y$10$LV9QJGAmK4foz/DsaviJwOxgCX0x/K84apZH9sit6xH1eAWhFDzI2', 'Phil Lafrance', 'Software, Music, Zen', 'I like simplicity'),
    (2, 'balls@balls', 'heya', '$2y$10$LV9QJGAmK4foz/DsaviJwOxgCX0x/K84apZH9sit6xH1eAWhFDzI2', 'Naheya', 'Software Developer', 'Hello I am cute and I like chicken wings.');
UNLOCK TABLES;

LOCK TABLES `links` WRITE;
INSERT INTO `links` VALUES
    (1, 1, 'facebook', 'AntSounds'),
    (2, 1, 'github', 'Phillaf'),
    (3, 1, 'lastfm', 'Phillaf'),
    (4, 1, 'linkedin', 'lafrancephilippe'),
    (5, 1, 'medium-subdomain', 'phillaf'),
    (6, 1, 'twitter', 'phillaf'),
    (7, 2, 'facebook', 'heya.na.26');
UNLOCK TABLES;
