This project is built for hype-beast code challenge.

## Requirement

- PHP 7.2
- MySQL (Or other database accepted by PHP Doctrine)
- Composer
- Memcached
- RabbitMQ
- Memcached PHP extension. See: https://pecl.php.net/package/memcache
- AMQP PHP Extension. See: https://pecl.php.net/package/amqp

## Installation
1. Download dependency by running

    `composer install`
   
2. Create a database, database user and it's password.
3. Copy the `.env` file to `.env.local`
4. Update DATABASE_URL. The format should be `DATABASE_URL=[schema]://[user]:[password]@[host]:[port]/[database_name]`
5. Update the database schema by running `php bin/console doctrine:schema:create`

You may want to override the RabbitMQ url, or Memcached URL if they are not hosted on default port

## Start

Running in development mode:  `php bin/console server:run localhost:8080`

To run in production environment:
1. Update `APP_ENV` to `prod` in `.env.local`
2. Deploy to Apache or Nginx. See https://symfony.com/doc/current/setup/web_server_configuration.html 

To handle C1K challenge, you must use production setting

## Run Test

Because of time constraints, currently only we have no test.

## 1K Request / Sec

