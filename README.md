Event database – the API
========================

Based on https://api-platform.com/


Import feeds
------------

Add feed import configurations in app/config/feeds.yml:

```
cp ~/Dropbox*/Projekter/events-database-api/app/config/feeds.yml app/config
```

Run console command to import events from feeds:

```
cd /vagrant/htdocs/app/console events:read:feeds
```

Security
--------

Create keys for JWT

```
mkdir -p app/var/jwt
openssl genrsa -out app/var/jwt/private.pem -aes256 4096
openssl rsa -pubout -in app/var/jwt/private.pem -out app/var/jwt/public.pem
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

curl --silent --verbose --request POST --header "Authorization: Bearer $token" http://event-database-api.vm/api/events --data @- <<'JSON'
{
"_format":"json",
"name":"test",
"endDate":"2100-01-01",
"startDate":"2000-01-01",
"description":"xxx"
}
JSON
