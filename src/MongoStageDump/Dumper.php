<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Luttkens
 * Date: 2014-08-27
 * Time: 11:18
 * To change this template use File | Settings | File Templates.
 */

namespace MongoStageDump;


class Dumper {

    private $_db;
    private $_conn;

    public function __construct()
    {
        $connection_string = getenv("MONGOLAB_URI_ENERGIMOLNET_PRODUCTION");
        $parsed = $this->parseConnectionString($connection_string);
        $this->_conn = new \MongoClient($connection_string);

        if (empty($parsed['database']))
            throw new \Exception("You must include a database in the connection string.");

        $this->_db = $this->_conn->selectDB($parsed['database']);
    }

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

    private function getCollections()
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

    /**
     * Will limit the dump to the last X rows of the collection.
     * Good to use for dumping parts of queues and logs.
     *
     * @param $collection
     * @param $limit
     */
    public function setLimit($collection, $limit)
    {
        # TODO: http://stackoverflow.com/questions/7828817/is-it-possible-to-mongodump-the-last-x-records-from-a-collection
    }

    public function parseConnectionString($connectionString)
    {
        $pattern = '(^mongodb://' .
            '(?:([^:]*):([^@]*)@)?' . # [username:password@]
            '([^/]*)' . # host1[:port1][,host2[:port2],...[,hostN[:portN]]]
            '(?:' .
            '/([^?]*)' . # /[database]
            '(?:[?](.*))?' . # [?options]
            ')?' .
            '$)';
        preg_match($pattern, $connectionString, $result);

        $result =  array(
            'username' => isset($result[1]) ? $result[1] : null,
            'password' => isset($result[2]) ? $result[2] : null,
            'host' => isset($result[3]) ? $result[3] : null,
            'database' => isset($result[4]) ? $result[4] : null,
            'options' => isset($result[5]) ? $result[5] : null,
        );

        $result['options'] = explode("&", $result['options']);
        foreach ($result['options'] as $key => &$param){
            $tmp = explode("=" , $param);
            if (count($tmp) == 2){
                $param = array($tmp[0] => $tmp[1]);
                $result['options'][$tmp[0]] = $tmp[1];
            }
            unset($result['options'][$key]);
        }

        return $result;

    }
}