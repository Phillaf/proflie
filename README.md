# README

Proflie is a hub for your online social media presence.

Example: http://phil.proflie.com  
Get your own on http://proflie.com

## How to run locally

```
cp .env.example .env
docker-compose up -d mysql
docker-compose run proflie php migration.php
docker-compose up -d proflie
```

Run the test suite like this
```
docker-compose exec proflie ./vendor/bin/phpunit tests
```

Run the linter like this
```
docker-compose exec proflie ./vendor/bin/phpcs --standard=psr12 --extensions=php ./src/
```

## Contribute

The following contributions are welcome:
- fixing bugs
- adding new social media
- improving tests
- improving user-experience
- improve performance, SEO and accessibility

Avoid unless absolutely necessary:
- Adding dependencies
- Making generics or extracting shared functions

## Mysql Migrations

There is a small homemade mysql migrations system in [migration.php](https://github.com/Phillaf/proflie/blob/main/migration.php). It executes the sql files under the `mysql-migration` folder sequentially. Each file is only run once, with the status tracked in the `migrations` mysql table. Those files should not be modified, only added, and should follow this filename format `yyyy-mm-dd-title.sql`.

## How to add a new type of social media

- Create a mysql migration file to update the enum on [links.social_media](https://github.com/Phillaf/proflie/blob/main/mysql-migration/2022-05-24-init.sql#L17)
- Create a html which will display the link [here](https://github.com/Phillaf/proflie/tree/main/src/Profile/links)
- Add your new social media to the admin drop-down [here](https://github.com/Phillaf/proflie/blob/main/src/Admin/CustomElements/LinkForm.js#L2)
