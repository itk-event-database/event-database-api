Event database – the API on platform.sh
=======================================

Environment variables
---------------------

The configuration in `app/config/parameters.yml` is read-only, but some variables must be set as [environment variables in platform.sh](https://docs.platform.sh/development/environment-variables.html):

Name | Description
-----|------------
itk:symfony:jwt_key_pass_phrase | The pass phrase used by JWT (see [Security](#security))
itk:symfony:secret | See http://symfony.com/doc/current/reference/configuration/framework.html#secret
itk:symfony:admin.base_url | The base url of the application, e.g. http://test-t6dnbai-7c5mshwfj6vdi.eu.platform.sh. Used for generating absolute urls.


Security
--------

Get `jwt_key_pass_phrase` from environment:

```
echo $PLATFORM_VARIABLES | base64 --decode | php -r "echo json_decode(stream_get_contents(STDIN))->{'itk:symfony:jwt_key_pass_phrase'}, PHP_EOL;"
```

Create keys for JWT using the pass phrase:

```
jwt_key_pass_phrase=$(echo $PLATFORM_VARIABLES | base64 --decode | php -r "echo json_decode(stream_get_contents(STDIN))->{'itk:symfony:jwt_key_pass_phrase'};")
mkdir -p var/jwt
openssl genrsa -out var/jwt/private.pem -aes256 -passout "pass:$jwt_key_pass_phrase" 4096
openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem -passin "pass:$jwt_key_pass_phrase"
```
