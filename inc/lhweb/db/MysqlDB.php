<?php
namespace lhweb\db;

/**
 * Description of MysqlDB
 *
 * @author loki
 */
class MysqlDB extends LHDB {
    public function __construct($dbhost, $dbname, $dbuser, $dbpass, $encoding = "utf8") {
        parent::__construct("mysql:host=$dbhost;dbname=$dbname; charset=$encoding", 
                $dbhost, 
                $dbname, 
                $dbuser, 
                $dbpass, 
                $encoding, 
                array()
        );
    }
}
