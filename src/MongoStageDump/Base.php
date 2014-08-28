<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Luttkens
 * Date: 2014-08-28
 * Time: 11:03
 * To change this template use File | Settings | File Templates.
 */

namespace MongoStageDump;


class Base {

    protected $_db;
    protected $_conn;

    protected $_username;
    protected $_password;
    protected $_database;


    public function __construct($connection_string)
    {
        $parsed = $this->parseConnectionString($connection_string);
        $this->_conn = new \MongoClient($connection_string);

        if (empty($parsed['database']))
            throw new \Exception("You must include a database in the connection string.");

        # Use the secondary to dump data
        $this->_conn->setReadPreference(\MongoClient::RP_SECONDARY);
        $this->_db = $this->_conn->selectDB($parsed['database']);
    }

    public function getSecondaryHost()
    {
        foreach ($this->_conn->getHosts() as $host){
            if ($host['state'] == 2)
                return "{$host['host']}:{$host['port']}";
        }
        throw new \Exception("Could not find the secondary host.");
    }


    protected function getTempDir()
    {
        return "D:/temp/dump/";
        return sys_get_temp_dir();
    }

    public function getPrimaryHost()
    {
        foreach ($this->_conn->getHosts() as $host){
            if ($host['state'] == 1)
                return "{$host['host']}:{$host['port']}";
        }
        throw new \Exception("Could not find the primary host.");
    }
    /**
     * Returns true if no errors occured.
     *
     * @param $cmd
     * @param null $out set this to capture the out put
     * @param null $errors set this to capture error output
     * @return bool
     */

    protected function cmd($cmd, &$out = null, &$errors = null)
    {
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "w"),  // stderr
        );

        $process = proc_open($cmd, $descriptorspec, $pipes, dirname(__FILE__), null);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        proc_close($process);

        if (!is_null($out)) $out = $stdout;
        if (!is_null($errors)) $errors = $stderr;
        return empty($stderr);
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

        $this->_username = $result['username'];
        $this->_password = $result['password'];
        $this->_database = $result['database'];

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

    public function log($msg){
        echo trim($msg)."\n";
    }
}