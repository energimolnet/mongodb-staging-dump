<?php

error_reporting(E_ALL & ~E_STRICT);

require_once(dirname(__FILE__).'/../src/MongoStageDump/Autoloader.php');
/*
OAuth2\Autoloader::register();
*/


// register vendors if possible
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require_once(__DIR__.'/../vendor/autoload.php');
}

// register test classes

