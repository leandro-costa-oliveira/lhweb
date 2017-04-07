<?php
namespace lhweb\controller;

use lhweb\database\LHDB;
use lhweb\database\AbstractEntity;

abstract class AbstractController {
    /**
     *
     * @var AbstractEntity
     */
    protected $entityClass;
    
    public abstract function getDaoInstance();
            
    function __construct() {
        $this->db  = LHDB::getConnection();
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function primeiro(){
        $c = $this->entityClass;
        return $c::primeiro();
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function ultimo(){
        $c = $this->entityClass;
        return $c::ultimo();
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function anterior($pk){
        $c = $this->entityClass;
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
        $c = $this->entityClass;
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
        $c = $this->entityClass;
        return $c::getByPK($pk);
    }
    
    /**
     * 
     * @return int
     * @throws RegistroNaoEncontrado
     */
    public function Apagar($pk){
        $c = $this->entityClass;
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
        $c = $this->entityClass;
        return $c::salvar($obj);
    }
}
