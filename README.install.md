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
app/console fos:user:create api api@example.com apipass
```

Test the API using username and password to get a token:

```
token=$(curl --silent --request POST http://event-database-api.vm/api/login_check -d _username=api -d _password=apipass | sed 's/{"token":"\(.*\)"}/\1/')
echo $token
curl --silent --header "Authorization: Bearer $token" http://event-database-api.vm/api/events
```
