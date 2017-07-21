<?php
namespace lhweb\database;

/**
 * Description of MysqlDB
 *
 * @author loki
 */
class MysqlDB extends LHDB {
    public function __construct($dbhost, $dbname, $dbuser, $dbpass, $encoding = "utf8") {
        parent::__construct("mysql:host=$dbhost;dbname=$dbname; charset=$encoding", 
                $dbuser, 
                $dbpass, 
                array()
        );
    }
    
    public function select_db($dbname) {
        $this->exec("USE $dbname");
    }
}
