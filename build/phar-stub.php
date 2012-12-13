<?php
/**
 * Copyright 2010-2012 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

Phar::mapPhar('aws.phar');

define('AWS_PHAR', true);

require_once 'phar://aws.phar/vendor/symfony/class-loader/Symfony/Component/ClassLoader/UniversalClassLoader.php';

$classLoader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$classLoader->registerNamespaces(array(
    'Aws'      => 'phar://aws.phar/src',
    'Guzzle'   => 'phar://aws.phar/vendor/guzzle/guzzle/src',
    'Symfony\\Component\\EventDispatcher' => 'phar://aws.phar/vendor/symfony/event-dispatcher',
    'Doctrine' => 'phar://aws.phar/vendor/doctrine/common/lib',
    'Monolog'  => 'phar://aws.phar/vendor/monolog/monolog/src'
));
$classLoader->register();

// Many AWS services require a more up to date cacert.pem file. Because curl cannot read directly from a cacert file,
// here we are attempting to copy the cacert file from the phar into a tmp writable directory.
$cacert = sys_get_temp_dir() . '/cacert.pem';
if (!file_exists($cacert)) {
    if (!@file_put_contents($cacert, file_get_contents('phar://aws.phar/vendor/guzzle/guzzle/src/Guzzle/Http/Resources/cacert.pem'))) {
        trigger_error("Could not write cacert file to {$cacert}", E_USER_NOTICE);
    } else {
        define('AWS_CACERT', $cacert);
    }
} else {
    define('AWS_CACERT', $cacert);
}

return $classLoader;

__HALT_COMPILER();
