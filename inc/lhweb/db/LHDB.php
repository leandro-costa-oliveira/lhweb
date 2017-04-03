<?php
namespace lhweb\db;
use \PDO;

/**
 * Represents the connection with the database,
 * actially using PDO, but it works as a layer 
 * of transparency.
 *
 * @author loki
 */
abstract class LHDB extends PDO {
    protected $dburl;
    protected $dbhost;
    protected $dbname;
    protected $dbuser;
    protected $dbpass;
    protected $dbopt;
    protected $encoding;
    protected $autoCommit = false;


    /**
     *
     * @var PDO
     */
    protected $pdo;
    
    public function __construct($dburl, $dbhost, $dbname, $dbuser, $dbpass, $encoding="utf8", $dbopt=null){
        parent::__construct($dburl, $dbuser, $dbpass, $dbopt);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->dburl = $dburl;
        $this->dbhost = $dbhost;
        $this->dbname = $dbname;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
        $this->dbopt  = $dbopt;
        $this->encoding = $encoding;
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
     * @return \lhweb\db\GenericQuerys
     */
    public function query($table){
        return new GenericQuery($this, $table, null);
    }
    
    /**
     * 
     * @param \lhweb\db\AbstractEntity $entity
     * @return \lhweb\db\GenericQuery
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
        $nrows = $this->pdo->exec($sql);
        
        if($this->autoCommit) {
            $this->commit();
        }
        
        return $nrows;
    }
}
