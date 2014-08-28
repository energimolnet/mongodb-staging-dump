<?php


/*
 * Set environmental variables based on config
 */
$args = getArgs();

if (isset($args['config']) && isset($args['config']['ENV'])){
    foreach ($args['config']['ENV'] as $name => $value)
        putenv("$name=$value");
}

error_reporting(E_ALL & ~E_STRICT);

require 'vendor/autoload.php';

/*
 * Any code that should be run with this script
 */

