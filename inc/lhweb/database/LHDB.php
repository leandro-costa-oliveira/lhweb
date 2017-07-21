<?php
namespace lhweb\database;
use \PDO;

/**
 * Represents the connection with the database,
 * actially using PDO, but it works as a layer 
 * of transparency.
 *
 * @author loki
 */
abstract class LHDB extends PDO {
    /**
     *
     * @var LHDB[]
     */
    protected static $conexoes = array();
    
    /**
     * 
     * @param int $idx
     * @return LHDB
     */
    public static function getConnection($idx=0){
        if(array_key_exists($idx, LHDB::$conexoes)){
            return LHDB::$conexoes[$idx];
        } else {
            throw new \Exception("Conexão com o Banco de Dados não encontrada.");
        }
    }
    
    public function __construct($dburl, $dbuser, $dbpass, $dbopt=null){
        parent::__construct($dburl, $dbuser, $dbpass, $dbopt);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        array_push(LHDB::$conexoes, $this);
    }
    
    
    function commit(){
        if($this->inTransaction()){
            return parent::commit();
        }
    }
    
    function rollBack(){
        if($this->inTransaction()){
            return parent::rollBack();
        }
    }
    
    /**
     * 
     * @param type boolean
     * @return boolean
     */
    function setAutoCommit($autoCommit){
        return $this->setAttribute(PDO::ATTR_PERSISTENT, $autoCommit);
    }
    
    /**
     * 
     * @param type string
     * @return \lhweb\database\GenericQuery
     */
    public function query($table){
        return new GenericQuery($this, $table, null);
    }
    
    /**
     * 
     * @param type string
     * @return \lhweb\database\GenericQuery
     */
    public function pdoquery($stm){
        return parent::query($stm);
    }
    
    /**
     * 
     * @param \lhweb\database\AbstractEntity $entity
     * @return \lhweb\database\GenericQuery
     */
    public function queryEntity(AbstractEntity $entity){
        return new GenericQuery($this, $entity->getTableName(), $entity);
    }
    
    /**
     * 
     * @param type string
     * @return int
     */
    public function exec($sql) {
        $nrows = parent::exec($sql);
        return $nrows;
    }
    
    public function select_db($dbname) {
        error_log("LHDB::SELECT DB STUB [$dbname]");
    }
}
