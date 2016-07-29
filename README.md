Event database – the API
========================

Based on https://api-platform.com/

Installation
------------

```
composer install
bin/console doctrine:database:create
bin/console doctrine:schema:create
```

Security
--------

Create keys for JWT with password (test) matching jwt_key_pass_phrase in parameters.yml:

```
mkdir -p app/var/jwt
openssl genrsa -out app/var/jwt/private.pem -aes256 -passout pass:test 4096
openssl rsa -pubout -in app/var/jwt/private.pem -out app/var/jwt/public.pem -passin pass:test
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
  "occurrences": [ {
    "startDate": "2000-01-01"
  } ]
}
JSON
```

Get all events:

```
curl --silent --verbose --request GET --header "Authorization: Bearer $token" http://event-database-api.vm/api/events
```


Import feeds
------------

Add feed import configurations in app/config/feeds.yml:

```
cp ~/Dropbox*/Projekter/events-database-api/app/config/feeds.yml app/config
```

Load feed configurations into database:

```
bin/console doctrine:fixtures:load --append --no-interaction
```

Run console command to import events from feeds:

```
bin/console events:read:feeds
```
