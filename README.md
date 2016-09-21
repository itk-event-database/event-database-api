Event database – the API
========================

Based on https://api-platform.com/

Installation
------------

```
./install.sh 

vagrant up
vagrant ssh

cd /vagrant/htdocs
mysql -u root -e "create database symfony"
composer install
bin/console doctrine:migrations:migrate
```

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

Test the API using username and password to get a token:

```
token=$(curl --silent --request POST http://event-database-api.vm/api/login_check --data _username=api-write --data _password=apipass | sed 's/{"token":"\(.*\)"}/\1/')
echo $token
curl --silent --verbose --header "Authorization: Bearer $token" http://event-database-api.vm/api/events
```

Create an event:

```
curl --silent --verbose --request POST --header "Authorization: Bearer $token" http://event-database-api.vm/api/events --data @- <<'JSON'
{
  "_format":"json",
  "name":"Big bang",
  "description":"The first event",
  "langcode": "en",
  "occurrences": [ {
    "startDate": "2000-01-01",
    "startDate": "2001-01-01",
	"place": {
	  "name": "Some place"
	}
  } ]
}
JSON
```

Get all events:

```
curl --silent --verbose --request GET --header "Authorization: Bearer $token" http://event-database-api.vm/api/events
```

### Disabling security for development

Ypu may want to be able to access the api in your browser without having to provide credentials. To do this add

```
security:
    firewalls:
        dev:
            pattern: ^/
            security: false
```

at the bottom of app/config/config_dev.yml.


Running tests
-------------

First, clear out the test cache:

```
bin/console --env=test cache:clear
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


Import feeds
------------

Add feed import configurations in app/config/feeds.yml (Outside vagrant):

```
cp ~/Dropbox*/Projekter/events-database-api/app/config/feeds.yml htdocs/app/config
```

Load feed configurations into database (In vagrant):

```
bin/console doctrine:fixtures:load --append --no-interaction
```

Create a user for reading feeds (matching username and password in parameters.yml):

```
bin/console fos:user:create feed-reader feed-reader@example.com feed-reader
bin/console fos:user:promote feed-reader ROLE_ADMIN
```

Run console command to import events from feeds:

```
bin/console events:feeds:read
```

To read specific feed use either 'name' or 'id' as parameter:

```
bin/console events:feeds:read --name="Feed name"
bin/console events:feeds:read --id=3
```
