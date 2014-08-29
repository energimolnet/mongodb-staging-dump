<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Luttkens
 * Date: 2014-08-28
 * Time: 10:58
 * To change this template use File | Settings | File Templates.
 */

namespace MongoStageDump;


class Restore extends Base {

    public function __construct($connection_string)
    {
        parent::__construct($connection_string);

        if (strpos($this->_database, 'prod') !== false)
            throw new \Exception("Found the word 'prod' in connection string. Are your sure this is not the production database?");
    }


    /**
     * @param $collection
     * @param $path
     * @return bool
     * @throws \Exception
     */
    public function restoreCollection($collection, $path, $drop = true)
    {
        $time = microtime(true);
        $collection_filepath = "$path/$collection.bson";
        $this->log("Restore file $collection_filepath (" . filesize($collection_filepath) .")");
        $command = "mongorestore " .
            "--host {$this->getPrimaryHost()} " .
            "--username {$this->_username} " .
            "--password {$this->_password} " .
            "--collection $collection " .
            ($drop ? "--drop " : "") .
            "--db {$this->_database} " .
            "$collection_filepath ";

        $output = "";
        $errors = "";
        // This throws errors, even when the operation succeeds.
        $this->cmd($command, $output, $errors);
        $elapsed = microtime(true) - $time;
        $this->log("Restored collection $collection in $elapsed seconds");
    }

}