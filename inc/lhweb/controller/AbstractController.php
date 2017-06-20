<?php
namespace lhweb\controller;

use lhweb\database\AbstractEntity;
use lhweb\database\EntityArray;
use lhweb\exceptions\LHWebException;
use lhweb\exceptions\RegistroNaoEncontradoException;

abstract class AbstractController {
    protected static $entityClass = null;
    
    public function __construct() {
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public static function getEntityClass(){
        if(static::$entityClass===null){
            $class = explode("\\",strtolower(static::class));
            static::$entityClass = str_replace("controller", "", strtolower($class[count($class)-1]));
        }
        return static::$entityClass;
    }
    
    public function getPkName(){
        $c = $this->getEntityClass();
        return $c::getPkAttribute();
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
    public function getByPK($pk){
        $c = $this->getEntityClass();
        return $c::getByPK($pk);
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function mover($pk){
        $c = $this->getEntityClass();
        return $c::getByPK($pk);
    }
    
    /**
     * 
     * @return int
     * @throws RegistroNaoEncontradoException
     */
    public function apagar($pk){
        $c = $this->getEntityClass();
        $obj = $c::getByPK($pk);
        
        if($obj){
            return $obj->delete();
        } else {
            throw new RegistroNaoEncontradoException("PK:".htmlspecialchars($pk));
        }
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function salvar($obj){
        if($obj===null) {
            throw new RegistroNaoEncontrado();
        }
        return $obj->salvar();
    }
    
    public function listar($limit=0, $offset=0){
        $c = $this->getEntityClass();
        return $c::listar($limit, $offset);
    }
    
    /**
     * @param string $campo
     * @param string $valor
     * @return AbstractEntity
     */
    public function procurar($campo, $valor, $limit=0, $offset=0){
        $obj = $this->getEntityClass();
        $q = $obj::getBasicMoveQuery();
        
        if(is_array($campo)){
            $q->andWhere("(");
            foreach($campo as $c){
                if(strpos($c, ".")!==false){ // PROCURAR NOS JOINS
                    list($ftable, $c) = explode(".", $c);
                    foreach($obj::$joins as $cj => $join){
                        list($fk, $attr) = $join;
                        if($attr==$ftable){
                            $q->orWhere($cj::getNomeCampo($c,true))->like($valor, $cj::getTipoCampo($c));
                            break;
                        }
                    }
                } else { // É UM CAMPO DA PROPRIA CLASSE
                    $q->orWhere($obj::getNomeCampo($c,true))->like($valor, $obj::getTipoCampo($c));
                }
            }
            $q->Where(")");
        } else {
            if(strpos($campo, ".")!==false){ // PROCURAR NOS JOINS
                list($ftable, $campo) = explode(".", $campo);
                foreach($obj::$joins as $cj => $join){
                    list($fk, $attr) = $join;
                    if($attr==$ftable){
                        $q->andWhere($cj::getNomeCampo($campo,true))->like($valor, $cj::getTipoCampo($campo));
                        break;
                    }
                }
            } else { // É UM CAMPO DA PROPRIA CLASSE
                $q->andWhere($obj::getNomeCampo($campo,true))->like($valor, $obj::getTipoCampo($campo));
            } 
        } 
        
        if($limit) { $q->limit($limit); }
        if($offset) { $q->offset($offset); }
        
        return new EntityArray($q->getList(), $obj);
    }
    
    public function count(){
        $c = $this->getEntityClass();
        return $c::count();
    }
}
