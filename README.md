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
4. Update DATABASE_URL. The format should be 

   `DATABASE_URL=[schema]://[user]:[password]@[host]:[port]/[database_name]`
   
5. Update the database schema by running 

    `php bin/console doctrine:schema:create`

You may want to override the RabbitMQ url, or Memcached URL if they are not hosted on default port

## Start (development)

Running in development mode: 

`php bin/console server:run localhost:8080`

In addition to starting the server, you will need to start the messenger consumer in order to write to database.

To start 1 consumer worker, user the command: 

`php bin/console messenger:consume voter` 

## Start (production)

To run serer in production environment:
1. Update `APP_ENV` to `prod` in `.env.local`
2. Deploy to Apache or Nginx. See https://symfony.com/doc/current/setup/web_server_configuration.html 

To run worker in production environment, you will need a process monitor like Supervisor. See: https://symfony.com/doc/current/messenger.html#supervisor-configuration

For example, to spawn 20 workers on start:

    [program:messenger-consume]
     command=php /var/www/html/hype-beast/bin/console messenger:consume voter
     user=hype-beast
     numprocs=20
     autostart=true
     autorestart=true
     process_name=%(program_name)s_%(process_num)02d
     
To handle C1K challenge, you must use production setting

## Run Test

Because of time constraints, currently only we have no test.

## 1K Request / Sec

The following method is used to handle the C1K challenge:

1. The controller received an HTTP POST request
2. The controller send a message to the message queue
3. The controller queue the memcache for the poll result, if no cache is present, query the database and create a cache. The life time of the cache is specified by the CACHE_LIFE_TIME ( default 5 )env variable.
   
   Doing so reduce the amount fo SELECT query. The cache will only be updated after the cache expired. So at maximum should have only 1 SELECT query per 5 sec.
 
   The resulting query is at max 5 seconds behind. If true real time is needed, cache needs to be clear after message send, or disabled altogether. 
   
4. The workers will update the database after receiving a message from RabbitMQ.

   The number of workers can be scaled up as long as the system have the resource to do so.
   
In addition to caching and messenger queue, the web server itself need to be fine tuned as well.

The following configuration have been tested with JMeter.

- Linux server
- Apache2 2.4.29
- mpm_prefork
        
      <IfModule mpm_prefork_module>
          StartServers           500
          MinSpareServers        500
          MaxSpareServers        200
          MaxRequestWorkers     1000
          MaxConnectionsPerChild   0
          ServerLimit           1000
          Timeout                 60
          KeepAliveTimeout         1
      </IfModule>
      
- 20 Messenger worker

See `c1k_http_post.jmx` for test plan for JMeter

## Further scaling

It is possible to scale further on the request processing rate:

1. Create a Round Robin DNS records
2. Spawn multiple web server to consume HTTP request
3. Increase the messenger consumer records  
