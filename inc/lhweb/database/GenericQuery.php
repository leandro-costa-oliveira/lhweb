<?php
namespace lhweb\database;

/**
 * A Query Builder Class
 *
 * @author loki
 */
class GenericQuery {
    /**
     *
     * @var LHDB
     */
    protected $db = null;
    
    protected $table = null;
    protected $entity = null;
    protected $join = null;
    protected $group = null;
    protected $campos = array("*");
    protected $valores = array();
    protected $limit   = null;
    protected $offset  = null;
    protected $conditions = array(
        "where" => "",
        "having" => "",
    );
    protected $condition = "where";
    
    public function __construct(LHDB $db, $table, AbstractEntity $entity = null) {
        $this->db = $db;
        
        if($entity instanceof AbstractEntity){
            $this->entity = $entity;
            $this->table  = $entity->getTableName();
        } else {
            $this->table = $table;
        }
    }
    
    public function escapeValue($val){
        return $val;
    }
    
    public function campos(array $campos){
        $this->campos = $campos;
        return $this;
    }
    
    public function addCampo($campo){
        array_push($this->campos, $campo);
        return $this;
    }
    
    public function where($field){
        $this->condition = "where";
        $this->conditions[$this->condition] .= " $field";
        
        return $this;
    }
    
    public function whereAnd($field){
        return $this->where("AND $field");
    }
    
    public function whereOr($field){
        return $this->where("OR $field");
    }
    
    public function having($field){
        $this->condition = "having";
        $this->conditions[$this->condition] .= $field;
        
        return $this;
    }
    
    public function havingAnd($field){
        return $this->having("AND $field");
    }
    
    public function havingOr($field){
        return $this->having("OR $field");
    }
    
    public function basicCondition($op, $txt, $paramType) {
        $this->conditions[$this->condition] .= " $op :valores" . count($this->valores). " ";
        array_push($this->valores, array("v" => $txt, "t" => $paramType));
        return $this;
    }
    
    public function equals($txt, $paramType) {
        return $this->basicCondition("=", $txt, $paramType);
    }
    
    public function maiorQue($txt, $paramType) {
        return $this->basicCondition(">", $txt, $paramType);
    }
    
    public function maiorIgual($txt, $paramType) {
        return $this->basicCondition(">=", $txt, $paramType);
    }
    
    public function menorQue($txt, $paramType) {
        return $this->basicCondition("<", $txt, $paramType);
    }
    
    public function menorIgual($txt, $paramType) {
        return $this->basicCondition("<=", $txt, $paramType);
    }
    
    public function like($txt, $paramType) {
        return $this->basicCondition("LIKE", $txt, $paramType);
    }
    
    function getQuerySql(){
        $sql = "SELECT " . implode(",", $this->campos) . " FROM $this->table";
        if($this->join){$sql .= " $this->join "; }
        if(!empty($this->conditions["where"])) { $sql .= " WHERE " . $this->conditions["where"]; }
        if($this->group) { $sql .= " GROUP BY $this->group "; }
        if(!empty($this->conditions["having"])){ $sql .= " HAVING " . $this->conditions["having"]; }
        
        /*
        foreach($this->union_querys as $q2){
            $q2->orderby("");
            $sql .= " UNION " . $q2->getQuerySql();
        }*/
        
        //if($this->order) { $sql .= " ORDER BY $this->order "; }
        //if($this->limit ) {$sql .= " LIMIT $this->limit "; }
        //if($this->offset) {$sql .= " OFFSET $this->offset "; }
        return $sql;
    }
    
    function getList($entity = null){
        
        $stm = $this->db->prepare($this->getQuerySql());
        
        foreach($this->valores as $key => $val){
            $stm->bindParam(":valores$key", $val["v"], $val["t"]);
        }
        
        $stm->execute();
        
        if($entity == null){
            return $stm->fetchAll(LHDB::FETCH_ASSOC);
        } else {
            return $stm->fetchAll(LHDB::FETCH_CLASS, is_string($entity)?$entity:get_class($entity));
        }
    }
    
    function getSingle($entity = null){
        $stm = $this->db->prepare($this->getQuerySql() . " LIMIT 1");
        
        foreach($this->valores as $key => $val){
            $stm->bindParam(":valores$key", $val["v"], $val["t"]);
        }
        
        $stm->execute();
        
        if($entity == null){
            return $stm->fetch(LHDB::FETCH_ASSOC);
        } else {
            return $stm->fetch(LHDB::FETCH_CLASS, is_string($entity)?$entity:get_class($entity));
        }
    }
}

