# API Authentication #

```
composer require friendsofsymfony/user-bundle "~2.0@dev"
```

https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md

```
composer require lexik/jwt-authentication-bundle
```

```
mkdir -p app/var/jwt
openssl genrsa -out app/var/jwt/private.pem -aes256 4096
openssl rsa -pubout -in app/var/jwt/private.pem -out app/var/jwt/public.pem
```

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
curl --silent --header "Authorization: Bearer $token" http://event-database-api.vm/api/events
```

curl --silent --request POST --header "Authorization: Bearer $token" http://event-database-api.vm/api/events --data @- <<'JSON'
{
"_format":"json",
"name":"test",
"endDate":"2100-01-01",
"startDate":"2000-01-01",
"description":"xxx"
}
JSON
