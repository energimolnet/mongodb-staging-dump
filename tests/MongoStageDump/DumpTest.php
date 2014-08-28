<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Luttkens
 * Date: 2014-08-27
 * Time: 11:34
 * To change this template use File | Settings | File Templates.
 */

namespace MongoStageDump;


class DumpTest extends \PHPUnit_Framework_TestCase {

    public function test1()
    {
        $config = json_decode(file_get_contents("../../config.json"), true);

        $dump = new \MongoStageDump\Dumper($config['ENV']['MONGOLAB_URI_ENERGIMOLNET_PRODUCTION']);
        $restore = new \MongoStageDump\Restore($config['ENV']['MONGOLAB_URI_ENERGIMOLNET_STAGE']);


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
            'user_username' => ['$in' => $config['users']],
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

    }
}