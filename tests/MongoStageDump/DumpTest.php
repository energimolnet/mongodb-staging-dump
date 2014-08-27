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

    public function test1(){
        $dump = new \MongoStageDump\Dumper();

        $meters = [
            new \MongoId(),
            new \MongoId(),
            new \MongoId(),
            new \MongoId(),
        ];

        $dump->setDumpQuery('volumes', ['meter_id' => ['$in' => [$meters]]]);
    }
}