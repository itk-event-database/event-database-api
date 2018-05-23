<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

if ($relationships = getenv('PLATFORM_RELATIONSHIPS')) {
    $relationships = json_decode(base64_decode($relationships, true), true);

    foreach ($relationships['database'] as $endpoint) {
        if (empty($endpoint['query']['is_master'])) {
            continue;
        }

        $container->setParameter('database_driver', 'pdo_'.$endpoint['scheme']);
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

/*
 * Set some environment variables as Symfony parameters.
 *
 * Variables prefixed with itk:symfony: will be added as parameters.
 *
 * @see https://docs.platform.sh/administration/web/configure-environment.html#variables
 * @see https://docs.platform.sh/development/environment-variables.html
 */
if ($variables = getenv('PLATFORM_VARIABLES')) {
    $variables = json_decode(base64_decode($variables, true), true);

    foreach ($variables as $name => $value) {
        if (preg_match('/^itk:symfony:(?<name>.+)$/', $name, $matches)) {
            $container->setParameter($matches['name'], $value);
        }
    }
}
