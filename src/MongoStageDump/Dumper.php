<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Luttkens
 * Date: 2014-08-27
 * Time: 11:18
 * To change this template use File | Settings | File Templates.
 */

namespace MongoStageDump;


class Dumper extends Base {



    private $_dump_queries = [];


    /**
     * @return \MongoDB
     */

    public function getDb()
    {
        return $this->_db;
    }

    public function dump()
    {

    }


    /**
     * Gets all collections from the production database
     *
     */

    public function getCollections()
    {
        return $this->getDb()->listCollections();
    }

    /**
     * Sets a query that should be used as filter when
     * dumping a specific collection.
     *
     * If no query has been defined the full collection
     * will be dumped.
     *
     * @param $collection
     * @param $query
     */

    public function setDumpQuery($collection, $query)
    {
        $this->_dump_queries[$collection] = $query;
    }

    /**
     * Sets the collections that should be ignored in the dump
     *
     * @param $collection
     * @param bool $ignore
     */
    public function setIgnore($collection, $ignore = true)
    {

    }



    public function dumpCollection($collection)
    {
        $time = microtime(true);

        $tmp_dir = $this->getTempDir();

        $command = "mongodump " .
        "--host {$this->getSecondaryHost()} " .
        "--username {$this->_username} " .
        "--password {$this->_password} " .
        "--db {$this->_database} " .
        "--collection {$collection} " .
        "--out {$tmp_dir} ";

        if (isset($this->_dump_queries[$collection])){
            $json_query = \MongoJson::strict($this->_dump_queries[$collection]);
            $this->log("--query '$json_query' ");
            $command .= "--query '$json_query' ";
        }



        $output = "";
        $errors = "";
        if ($this->cmd($command, $output, $errors)){
            $elapsed = microtime(true) - $time;
            $this->log("Dumped collection $collection to $tmp_dir in $elapsed seconds");
            return "{$tmp_dir}/$this->_database";
        }else{
            throw new \Exception("There was an error executing the command: \n\n$errors");
        }

    }



    /**
     * Will limit the dump to the last X rows of the collection.
     * Good to use for dumping parts of queues and logs.
     *
     * @param $collection
     * @param $limit how many last documents to dump
     */

    public function setLimit($collection, $limit)
    {
        $cursor = $this->getDb()->selectCollection($collection)->find();
        $count = $cursor->count();

        if ($count>0){
            if ($count - $limit < 0)
                $limit = $count;
            $cursor->skip($count - $limit);
            $cursor->sort(['_id' => 1]);
            $cursor->limit(1);
            foreach ($cursor as $doc)
                $doc_id = $doc['_id'];

            if (!empty($doc_id))
                $this->setDumpQuery($collection, ['_id' => ['$gte' => $doc_id]]);
            else
                throw new \Exception("No documents found");
        }
    }

}