Event database – the API on platform.sh
=======================================

Security
--------

Get `jwt_key_pass_phrase` from environment:

```
echo $PLATFORM_VARIABLES | base64 --decode | php -r 'echo json_decode(stream_get_contents(STDIN))->jwt_key_pass_phrase, PHP_EOL;'
```

Create keys for JWT using the pass phrase:

```
jwt_key_pass_phrase=$(echo $PLATFORM_VARIABLES | base64 --decode | php -r 'echo json_decode(stream_get_contents(STDIN))->jwt_key_pass_phrase;')
mkdir -p var/jwt
openssl genrsa -out var/jwt/private.pem -aes256 -passout "pass:$jwt_key_pass_phrase" 4096
openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem -passin "pass:$jwt_key_pass_phrase"
```
