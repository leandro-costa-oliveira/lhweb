<?php
namespace lhweb\controller;

use lhweb\database\LHDB;
use lhweb\database\AbstractEntity;

abstract class AbstractController {
    protected static $entityClass = null;
    
    public static function getEntityClass(){
        if(static::$entityClass===null){
            $class = explode("\\",strtolower(static::class));
            static::$entityClass = str_replace("controller", "", strtolower($class[count($class)-1]));
        }
        return static::$entityClass;
    }
            
    function __construct() {
        $this->db  = LHDB::getConnection();
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function primeiro(){
        $c = $this->getEntityClass();
        return $c::primeiro();
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function ultimo(){
        $c = $this->getEntityClass();
        return $c::ultimo();
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function anterior($pk){
        $c = $this->getEntityClass();
        $ret = $c::anterior($pk);
        if(!$ret){
            $ret = $c::ultimo();
        }
        
        return $ret;
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function proximo($pk){
        $c = $this->getEntityClass();
        $ret = $c::proximo($pk);
        if(!$ret){
            $ret = $c::primeiro();
        }
        return $ret;
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function Mover($pk){
        $c = $this->getEntityClass();
        return $c::getByPK($pk);
    }
    
    /**
     * 
     * @return int
     * @throws RegistroNaoEncontrado
     */
    public function Apagar($pk){
        $c = $this->getEntityClass();
        $obj = $c::getByPK($pk);
        
        if($obj){
            return $c::delete($obj);
        } else {
            throw new RegistroNaoEncontrado();
        }
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function Salvar($obj){
        $c = $this->getEntityClass();
        return $c::salvar($obj);
    }
}
