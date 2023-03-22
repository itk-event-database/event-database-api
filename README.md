Event database – the API
========================

[![Review](https://github.com/itk-event-database/event-database-api/actions/workflows/pr.yml/badge.svg)](https://github.com/itk-event-database/event-database-api/actions/workflows/pr.yml)

Based on [API Platform v.2.2](https://api-platform.com/docs/v2.2/core/)

## Development setup with docker

```sh
git clone --branch=develop https://github.com/itk-event-database/event-database-api event-database-api
cd event-database-api

docker compose up --detach
docker compose exec phpfpm composer install --no-interaction
docker compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```

### Run tests

```sh
docker compose exec phpfpm bin/console --env=test cache:clear
docker compose exec phpfpm bin/console --env=test doctrine:database:create --no-interaction --if-not-exists
docker compose exec phpfpm vendor/bin/behat
```

### Access the site

Edit `web/app_dev.php` and remove or edit the lines denying access to
`app_dev.php`:

```php
// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'], true) || PHP_SAPI === 'cli-server')
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}
```

```sh
# Get url (with port) from docker
export event_database_api_url="http://event-database-api.local.computer:$(docker compose port reverse-proxy 80 | cut -d: -f2)"
echo $event_database_api_url
curl --silent --header "accept: application/ld+json" $event_database_api_url/api/events
```

**Note**: Remember to run all `bin/console` commands in the following sections
in the `phpfpm` docker container, i.e. prepend them with `docker compose exec
phpfpm bin/console`.

Generated images
----------------

[LiipImagineBundle](https://symfony.com/doc/2.0/bundles/LiipImagineBundle/index.html)
is used to generate scaled images. Images are generated whenever
images are changed or added, but to get things started you have to
generate all images from the command line.

To add generated images to all Events and Places, run

```
bin/console admin:images:set AppBundle:Event
bin/console admin:images:set AppBundle:Place
```

To reset generated images, add `--reset` to the commands:

```
bin/console admin:images:set --reset AppBundle:Event
bin/console admin:images:set --reset AppBundle:Place
```

To remove all generated images (cf. [LiipImagineBundle Console
Commands](https://symfony.com/doc/2.0/bundles/LiipImagineBundle/commands.html#remove-cache)),
run

```
bin/console liip:imagine:cache:remove
```

Coding standard
---------------

The [Symfony Coding Standards](https://symfony.com/doc/3.4/contributing/code/standards.html) apply.

To check the code, run:

```
composer check-coding-standards
```

To apply the coding standards, run:

```
composer apply-coding-standards
```

Remember to [*run all tests after applying coding standards*](#running-tests).


API documentation
-----------------

Go to http://event-database-api.vm/api/doc to see automatically generated API documentation.

The API spec is also exported as `/web/api/api-spec-v1.json`. A job in github actions
checks if there are changes to the api spec and fails if there is.

If your changes are intentional you can re-export an updated API spec by running
`docker compose exec phpfpm composer update-api-spec` and commint the changes.

Security
--------

Create keys for JWT with password (test) matching jwt_key_pass_phrase in parameters.yml:

```
mkdir -p var/jwt
openssl genrsa -out var/jwt/private.pem -aes256 -passout pass:test 4096
openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem -passin pass:test
```

Create test users

```
bin/console fos:user:create api-read api-read@example.com apipass
bin/console fos:user:promote api-read ROLE_API_READ

bin/console fos:user:create api-write api-write@example.com apipass
bin/console fos:user:promote api-write ROLE_API_WRITE
```

Create admin user:

```
bin/console fos:user:create admin admin@example.com password
bin/console fos:user:promote admin ROLE_SUPER_ADMIN
```

#### Important note for Apache users

Apache server [will strip](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md#important-note-for-apache-users) any `Authorization header` not in a valid HTTP BASIC AUTH format.

To use the authorization header mode of LexikJWTAuthenticationBundle, please add those rules to your VirtualHost configuration:

```apache
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

Using the API
-------------

### Get events

Get future events:

```
curl --silent --verbose --request GET --header "Accept: application/ld+json" http://event-database-api.vm/api/events
```

By default, only future events are returned and to get all events (incl. past events) you must specify a start date query.

To get all events after 2001-01-01 use a query like this:

```
curl --silent --verbose --request GET --header "Accept: application/ld+json" http://event-database-api.vm/api/events?occurrences.startDate[after]=2001-01-01
```


Make the output more human readable by formatting the JSON:

```
curl --silent --request GET --header "Accept: application/ld+json" http://event-database-api.vm/api/events | php -r 'echo json_encode(json_decode(stream_get_contents(STDIN)), JSON_PRETTY_PRINT);'
```

Test the API using username and password to get a token:

```
token=$(curl --silent --request POST http://event-database-api.vm/api/login_check --data _username=api-write --data _password=apipass | php -r 'echo json_decode(stream_get_contents(STDIN))->token;')
echo $token
```

### Create an event

```
curl --silent --verbose --request POST --header "Authorization: Bearer $token" --header "Content-type: application/ld+json" --header "Accept: application/ld+json" http://event-database-api.vm/api/events --data @- <<'JSON'
{
  "name":"Big bang",
  "description":"The first event",
  "image": "https://dummyimage.com/600x400/000/00ffd5.png",
  "langcode": "en",
  "occurrences": [ {
    "startDate": "2000-01-01",
    "endDate": "2001-01-01",
    "place": {
      "name": "Some place"
    }
  } ]
}
JSON
```

### Uploading files

```
# Get image
curl http://lorempixel.com/400/200/ > /tmp/image.jpg
# Upload image
curl --silent --request POST --header "Authorization: Bearer $token" --form file=@/tmp/image.jpg http://event-database-api.vm/api/upload
```

Running tests
-------------

First, clear out the test cache and create the test database:

```
bin/console --env=test cache:clear
bin/console --env=test doctrine:database:create
```

Run all behat tests like this:

```
vendor/bin/behat
```

or run some tests like this, say:

```
vendor/bin/behat features/events.feature
```

To run unit tests:

```
vendor/bin/phpunit
```


Importing feed configurations
-----------------------------

Create/update feed configurations from yaml files:

```
bin/console events:feed:import --help
```

Previewing feed data
--------------------

```
bin/console events:feed:preview --help
```

Reading feeds
-------------


Run console command to import events from feeds:

```
bin/console events:feeds:read
```

To read specific feed use either 'name' or 'id' as parameter:

```
bin/console events:feeds:read --name="Feed name"
bin/console events:feeds:read --id=3
```

### Reading all feed using `cron`

```
0 * * * * SYMFONY_ENV=prod bin/read-all-feeds
```

Loading fixtures
----------------

Add feed import configuration files (Outside vagrant, from htdocs directory):

```
mkdir -p src/AdminBundle/DataFixtures/Data
cp ~/Dropbox*/Projekter/events-database-api/fixtures/*.yml src/AdminBundle/DataFixtures/Data/
```

Load feed configurations into database:

```
bin/console doctrine:fixtures:load --append --no-interaction
```

Create a user for reading feeds (matching username and password in parameters.yml):

```
bin/console fos:user:create feed-reader feed-reader@example.com feed-reader
bin/console fos:user:promote feed-reader ROLE_ADMIN
```
