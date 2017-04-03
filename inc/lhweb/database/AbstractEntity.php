<?php
namespace lhweb\database;

/**
 * Description of AbstractEntity
 *
 * @author loki
 */
abstract class AbstractEntity {
    /**
     * 
     * @return string the name of the database
     */
    public function getTableName(){
        $c = explode("\\",get_class($this));
        return $c[count($c)-1];
    }
    
    public function getPkName(){
        return "ID";
    }
}
