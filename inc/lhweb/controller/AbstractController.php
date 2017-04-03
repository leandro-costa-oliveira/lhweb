<?php
namespace lhweb\controller;

use \lhweb\database\LHDB;
use \lhweb\database\GenericQuerys;
use \lhweb\database\AbstractEntity;

abstract class AbstractController {
    /**
     *
     * @var HDB
     */
    protected $db;
    
    public abstract function getDaoInstance();
            
    function __construct(LHDB $db) {
        $this->db  = $db;
        $this->dao = $this->getDaoInstance();
        assert($this->dao);
    }
    
    /**
     * 
     * @return string 
     * Returns the default table name, as the classe name without Controller.
     */
    public function getTableName(){
        $c = explode("\\",get_class($this));
        return strtoupper(str_replace("Controller","", $c[count($c)-1]));
    }
    
    /**
     * 
     * @return string
     * Retorta o nome do campo de chave primária na tabela.
     */
    public function getPkName () {
        return "ID";
    }
    
    
    /**
     * 
     * @return GenericQuerys
     */
    public function getBasicMoveQuery(){
        return $this->db->query($this->getTableName());
    }
    
    /**
     * 
     * @param type $pk
     * @return AbstractEntity
     */
    public function getByPk($pk){
        $q      = $this->getBasicMoveQuery();
        $campo  = ($q->getWhere()?" AND ":"") .$this->getTableName() . "." . $this->getPkName();
        $rs     = $q->where($campo)->equal($pk)->getSingle();
        return $this->makeObjectFromRs($rs);
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function primeiro(){
        $rs = $this->getBasicMoveQuery()
                ->orderby($this->getTableName() . "." . $this->getPkName())
                ->getSingle();
        return $this->makeObjectFromRs($rs);
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function ultimo(){
        $rs = $this->getBasicMoveQuery()
                ->orderby($this->getTableName() . "." . $this->getPkName(),"DESC")
                ->getSingle();
        return $this->makeObjectFromRs($rs);
    }
    
    /**
     * 
     * @param type $pk
     * @return AbstractEntity
     */
    public function proximo($pk){
        $q      = $this->getBasicMoveQuery();
        $campo  = ($q->getWhere()?" AND ":"") .$this->getTableName() . "." . $this->getPkName();
        $rs     = $q->where($campo)->maiorQue(intval($pk))->getSingle();
        return $this->makeObjectFromRs($rs);
    }
    
    /**
     * 
     * @param type $pk
     * @return AbstractEntity
     */
    public function anterior($pk){
        $q      = $this->getBasicMoveQuery();
        $campo  = ($q->getWhere()?" AND ":"") .$this->getTableName() . "." . $this->getPkName();
        $rs     = $q->where($campo)->menorQue(intval($pk))
                ->getSingle();
        return $this->makeObjectFromRs($rs);
    }
    
    /**
     * 
     * @param string $campo
     * @param type $txt
     * @return array
     */
    public function procurar($campo, $txt) {
        $lista = array();
        if($campo){
            $q      = $this->getBasicMoveQuery();
            $campo  = ($q->getWhere()?" AND ":"") . $this->db->escape($campo);
            foreach($q->where($campo)->like($txt)->getList() as $rs){
                array_push($lista, $this->makeObjectFromRs($rs));
            }
        } else {
            foreach($this->getBasicMoveQuery()->limit(10)->getList() as $rs){
                array_push($lista, $this->makeObjectFromRs($rs));
            }
        }
        
        return $lista;
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function salvar($obj){
        if(property_exists($obj, $this->getPkName()) && isset($obj->$this->getPkName())){
            return $this->alterar($obj);
        } else {
            return $this->inserir($obj);
        }
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public abstract function makeObjectFromRs($rs);
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public abstract function inserir(AbstractEntity $obj);
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public abstract function alterar(AbstractEntity $obj);
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public abstract function apagar (AbstractEntity $obj);
}
