<?php
namespace lhweb\controller;

use lhweb\database\AbstractEntity;
use lhweb\database\EntityArray;
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
            $this->preApagar($obj);
            $ret = $obj->delete();
            $this->posApagar($obj);
            return $ret;
        } else {
            throw new RegistroNaoEncontradoException("PK:".htmlspecialchars($pk));
        }
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     */
    public function validar($obj){
        if($obj == null){
            throw new RegistroNaoEncontrado();
        }
    }
    
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function salvar($obj){
        $this->validar($obj);        
        $this->preSalvar($obj);

        $pkName = $obj::$primaryKey;
        if(property_exists($obj, $pkName) && !empty($obj->$pkName)){
            $pk = $obj->$pkName;
            
            $this->preUpdate($obj);
            $this->update($obj);
            
            $obj2 = $this->getByPK($pk);
            $this->posUpdate($obj2);
        } else {
            $this->preInsert($obj);
            $pk = $this->insert($obj);
            
            $obj2 = $this->getByPK($pk);
            $this->posInsert($obj2);
        }
        $this->posSalvar($obj2);
            
        return $obj2;
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function insert($obj) {
        return $obj->insert();
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function update($obj) {
        return $obj->update();
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function preUpdate($obj){
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function posUpdate($obj){
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function posInsert($obj){
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function preInsert($obj){
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function preSalvar($obj){
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function posSalvar($obj){
    }
    
    
    /**
     * 
     * @param AbstractEntity $obj
     */
    public function preApagar($obj){
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     */
    public function posApagar($obj){
    }
    
    public function listar($limit=0, $offset=0){
        $c = $this->getEntityClass();
        return $c::listar($limit, $offset);
    }
    
    function getNomeCampoProcura($obj, $campo) {
        if(strpos($campo, ".")!==false){ // PROCURAR NOS JOINS
            list($subCampo, $campo) = explode(".", $campo);
            
            $joinCount = 0;
            foreach($obj::$joins as $classJoin => $det) {
                list($campoJoin, $varName) = $det;
                if($subCampo==$varName){
                    return $classJoin::getNomeCampo($campo, true, "_$joinCount");
                }
                $joinCount++;
            }
            
            foreach($obj::$leftOuterJoins as $classJoin => $det) {
                list($campoJoin, $varName) = $det;
                if($subCampo==$varName){
                    return $classJoin::getNomeCampo($campo, true, "_$joinCount");
                }
                $joinCount++;
            }
        } else { // É UM CAMPO DA PROPRIA CLASSE
            return $obj::getNomeCampo($campo, true); 
        }
    }
    
    
    /**
     * @param string $campo
     * @param string $valor
     * @return AbstractEntity
     */
    public function procurar($campo, $valor, $limit=0, $offset=0){
        $obj = $this->getEntityClass();
        if(is_array($campo)){
            $q = $this->getQueryProcurarCampoArray($obj, $campo, $valor);
        } else {
            $q = $this->getQueryProcurarCampoString($obj, $campo, $valor);
        }
        
        if($limit) { $q->limit($limit); }
        if($offset) { $q->offset($offset); }
        
        return new EntityArray($q->getList(), $obj);
    }
    
    function getQueryProcurarCampoString($obj, $campo, $valor){
        $q = $obj::getBasicMoveQuery();
        $q->andWhere($this->getNomeCampoProcura($obj, $campo))->like($valor, $obj::getTipoCampo($campo));
        return $q;
    }
    
    function getQueryProcurarCampoArray($obj, $campos, $valor){
        $q = $obj::getBasicMoveQuery();
        
        $q->andWhere("(");
        foreach($campos as $campo){
            $q->orWhere($this->getNomeCampoProcura($obj, $campo))->like($valor, $obj::getTipoCampo($campo));
        }
        $q->Where(")");
        
        return $q;
    }
    
    /**
     * @param string $campo
     * @param string $valor
     * @return AbstractEntity
     */
    public function procurarCount($campo, $valor){
        $obj = $this->getEntityClass();
        if(is_array($campo)){
            $q = $this->getQueryProcurarCampoArray($obj, $campo, $valor);
        } else {
            $q = $this->getQueryProcurarCampoString($obj, $campo, $valor);
        }
        
        $q->campos(array("COUNT(" . $obj::$table . "." . static::getPkName() . ") as total"));
        
        $rs = $q->getSingle();
        return $rs["total"];
    }
    
    public function count(){
        $c = $this->getEntityClass();
        $o = new $c();
        return $o->count();
    }
}
