<?php

exec("chmod +x ./mongodump");

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

$dump = new \MongoStageDump\Dumper(getenv('MONGOLAB_URI_ENERGIMOLNET_PRODUCTION'));
$restore = new \MongoStageDump\Restore(getenv('MONGOLAB_URI_ENERGIMOLNET_STAGE'));


$dump->setLimit('edms_log', 500);
$dump->setLimit('file_jobs', 100);
$dump->setLimit('ediel_jobs', 100);
$dump->setLimit('scraper_jobs', 500);

foreach ($dump->getCollections() as $collection)
{
    /* @var $collection \MongoCollection */

    if (!in_array($collection->getName(), ['volumes','readings'])){
        $path = $dump->dumpCollection($collection->getName());
        $restore->restoreCollection( $collection->getName(), $path);
    }
}

/*
 * Dump volumes for certain users
 */

$cursor = $dump->getDb()->selectCollection('contracts')->find([
    'user_username' => ['$in' => $args['config']['users']],
]);

foreach ($cursor as $doc)
    $meters[] = $doc['meter_id'];

/**
 * Since command line arguments cannot be too big
 * we dump in chunks. Mongorestor will append new
 * documents.
 */

$chunk_size = 200;
for ($i=0;$i<count($meters);$i=$i+$chunk_size){
    $dump->setDumpQuery('volumes', ['meter_id' => ['$in' => array_slice($meters, $i, $chunk_size)]]);
    $path = $dump->dumpCollection('volumes');
    $restore->restoreCollection('volumes', $path,false);

    $dump->setDumpQuery('readings', ['meter_id' => ['$in' => array_slice($meters, $i, $chunk_size)]]);
    $path = $dump->dumpCollection('readings');
    $restore->restoreCollection('readings', $path, false);
}