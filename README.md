Event database – the API
========================

Based on https://api-platform.com/

Installation
------------

Get the code

```
git clone https://github.com/itk-event-database/event-database-api.git htdocs
```

Install

```
cd htdocs
composer install
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate --no-interaction
```

Install assets
```
bin/console assets:install
```

API documentation
-----------------

Go to http://event-database-api.vm/api/doc to see automatically generated API documentation.

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
vendor/symfony/symfony/phpunit
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
