<?php

if ($relationships = getenv('PLATFORM_RELATIONSHIPS')) {
  $relationships = json_decode(base64_decode($relationships), true);

  foreach ($relationships['database'] as $endpoint) {
    if (empty($endpoint['query']['is_master'])) {
      continue;
    }

    $container->setParameter('database_driver', 'pdo_' . $endpoint['scheme']);
    $container->setParameter('database_host', $endpoint['host']);
    $container->setParameter('database_port', $endpoint['port']);
    $container->setParameter('database_name', $endpoint['path']);
    $container->setParameter('database_user', $endpoint['username']);
    $container->setParameter('database_password', $endpoint['password']);
    $container->setParameter('database_path', '');
  }

  foreach ($relationships['redis'] as $endpoint) {
    $container->setParameter('resque.redis.host', $endpoint['host']);
    $container->setParameter('resque.redis.port', $endpoint['port']);
  }
}

/**
 * Set some environment variables as Symfony parameters.
 *
 * @see https://docs.platform.sh/administration/web/configure-environment.html#variables
 * @see https://docs.platform.sh/development/environment-variables.html
 */
if ($variables = getenv('PLATFORM_VARIABLES')) {
  $variables = json_decode(base64_decode($variables), true);

  foreach (['secret', 'jwt_key_pass_phrase', 'admin.base_url'] as $name) {
    if (isset($variables[$name])) {
      $container->setParameter($name, $variables[$name]);
    }
  }
}
