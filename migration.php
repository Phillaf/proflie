<?php

$mysqli = new mysqli('mysql', 'root', $_ENV["MYSQL_ROOT_PASSWORD"], $_ENV["MYSQL_DATABASE"]);

echo $mysqli->host_info . "\n";

$result = $mysqli->query("SELECT * FROM migrations");

if (!$result) {
    $query = <<< QUERY
CREATE TABLE `migrations` (
  `name` varchar(255) NOT NULL,
  `date_created` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`name`)
);
QUERY;
    $mysqli->query($query);
    $result = $mysqli->query("SELECT * FROM migrations");
}

$migrated = [];

foreach($result as $row) {
    $migrated[] = $row['name'];
}

$files = array_slice(scandir('./mysql-migration'), 2);

$toMigrate = array_diff($files, $migrated);

foreach ($toMigrate as $file) {
    $commands = file_get_contents("./mysql-migration/$file");
    $result = $mysqli->multi_query($commands);
    while ($mysqli->next_result());
    if (!$result) {
        echo "Failed to migrate $file. Stopping here\n";
        exit(1);
    } else {
        $result = $mysqli->query("INSERT INTO `migrations` (`name`) VALUES (\"$file\")");
        echo "migrated $file\n";
    }
}

echo "Migrations completed successfully\n";
