Event database – the API
========================

Based on https://api-platform.com/

Installation
------------

```
composer install
```

## Patches ##

We need to apply a couple of patches to [Handle circular references in DunglasApiParser](https://github.com/nelmio/NelmioApiDocBundle/commit/c1c711bc26fd5f74a94923f93b11153ede6d06be):

```
cd vendor/nelmio/api-doc-bundle/Nelmio/ApiDocBundle
curl --silent https://github.com/nelmio/NelmioApiDocBundle/commit/abb100b29b54ae0167fc0cfbea5a3db762d56c8b.patch | patch --strip=1
curl --silent https://github.com/nelmio/NelmioApiDocBundle/commit/c1c711bc26fd5f74a94923f93b11153ede6d06be.patch | patch --strip=1
cd -
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
app/console fos:user:create api-read api-read@example.com apipass
app/console fos:user:promote api-read ROLE_API_READ

app/console fos:user:create api-write api-write@example.com apipass
app/console fos:user:promote api-write ROLE_API_WRITE
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
  "name":"test",
  "endDate":"2100-01-01",
  "startDate":"2000-01-01",
  "description":"xxx"
}
JSON
```


Import feeds
------------

Add feed import configurations in app/config/feeds.yml:

```
cp ~/Dropbox*/Projekter/events-database-api/app/config/feeds.yml app/config
```

Load feed configurations into database:

```
app/console doctrine:fixtures:load --append --no-interaction
```

Run console command to import events from feeds:

```
app/console events:read:feeds
```
