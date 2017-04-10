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
    
    /**
     *
     * @var string
     */
    protected $table = null;
    protected $join = null;
    protected $campos = array("*");
    protected $camposSet= array();
    protected $union  = array();
    protected $valores = array();
    protected $valoresSet = array();
    protected $tiposSet = array();
    protected $limit   = null;
    protected $offset  = null;
    protected $orderBy  = null;
    protected $groupBy = null;
    protected $conditions = array(
        "where" => "",
        "having" => "",
    );
    protected $condition = "where";
    
    public function __construct(LHDB $db, $table) {
        $this->db = $db;
        $this->table = $table;
    }
    
    public function campos(array $campos){
        $this->campos = $campos;
        return $this;
    }
    
    public function addCampo($campo){
        array_push($this->campos, $campo);
        return $this;
    }
    
    public function where($valor){
        $this->condition = "where";
        $this->conditions[$this->condition] .= " $valor";
        
        return $this;
    }
    
    public function andWhere($valor){
        if($this->conditions[$this->condition]){
            return $this->where("AND $valor");
        } else {
            return $this->where("$valor");
        }
    }
    
    public function orWhere($valor){
        if($this->conditions[$this->condition]){
            return $this->where("OR $valor");
        } else {
            return $this->where("$valor");
        }
    }
    
    public function having($valor){
        $this->condition = "having";
        $this->conditions[$this->condition] .= $$valor;
        
        return $this;
    }
    
    public function andHaving($valor){
        if($this->conditions[$this->condition]){
            return $this->having("AND $valor");
        } else {
            return $this->having("$valor");
        }
    }
    
    public function orHaving($valor){
        if($this->conditions[$this->condition]){
            return $this->having("OR $valor");
        } else {
            return $this->having("$valor");
        }
    }
    
    public function basicCondition($op, $txt, $paramType) {
        $this->conditions[$this->condition] .= " $op :valores" . count($this->valores). " ";
        array_push($this->valores, array("v" => $txt, "t" => $paramType));
        return $this;
    }
    
    public function equals($txt, $paramType=LHDB::PARAM_STR) {
        return $this->basicCondition("=", $txt, $paramType);
    }
    
    public function maiorQue($txt, $paramType=LHDB::PARAM_STR) {
        return $this->basicCondition(">", $txt, $paramType);
    }
    
    public function maiorIgual($txt, $paramType=LHDB::PARAM_STR) {
        return $this->basicCondition(">=", $txt, $paramType);
    }
    
    public function menorQue($txt, $paramType=LHDB::PARAM_STR) {
        return $this->basicCondition("<", $txt, $paramType);
    }
    
    public function menorIgual($txt, $paramType=LHDB::PARAM_STR) {
        return $this->basicCondition("<=", $txt, $paramType);
    }
    
    public function like($txt, $paramType=LHDB::PARAM_STR) {
        return $this->basicCondition("LIKE", $txt, $paramType);
    }
    
    public function orderBy($txt, $dir="ASC"){
        $this->orderBy .= "$txt $dir";
        return $this;
    }
    
    function getQuerySql(){
        $sql = "SELECT " . implode(",", $this->campos) . " FROM $this->table";
        
        if($this->join){$sql .= " $this->join "; }
        if(!empty($this->conditions["where"])) { $sql .= " WHERE " . $this->conditions["where"]; }
        if($this->groupBy) { $sql .= " GROUP BY $this->groupBy "; }
        if(!empty($this->conditions["having"])){ $sql .= " HAVING " . $this->conditions["having"]; }
        
        foreach($this->union as $u){
            $u->orderBy("");
            $sql .= " UNION " . $u->getQuerySql();
        }
        
        if($this->orderBy){ $sql .= " ORDER BY $this->orderBy "; }
        if($this->limit ) { $sql .= " LIMIT  $this->limit "; }
        if($this->offset) { $sql .= " OFFSET $this->offset "; }
        return $sql;
    }
    
    function bindQueryParameters($stm){
        if(count($this->valores)>0){
            foreach($this->valores as $key => $val){
                $stm->bindValue(":valores$key", $val["v"], $val["t"]);
            }
        }
    }
    
    function getList($entity = null){
        
        $stm = $this->db->prepare($this->getQuerySql());
        $this->bindQueryParameters($stm);
        $stm->execute();
        
        if($entity == null){
            return $stm->fetchAll(LHDB::FETCH_ASSOC);
        } else {
            return $stm->fetchAll(LHDB::FETCH_CLASS, is_string($entity)?$entity:get_class($entity));
        }
    }
    
    function getSingle($entity = null){
        $stm = $this->db->prepare($this->getQuerySql() . " LIMIT 1");
        $this->bindQueryParameters($stm);
        
        
        if($entity == null){
            $stm->execute();
            return $stm->fetch(LHDB::FETCH_ASSOC);
        } else {
            $stm->setFetchMode(LHDB::FETCH_CLASS, is_string($entity)?$entity:get_class($entity));
            $stm->execute();
            return $stm->fetch(LHDB::FETCH_CLASS);
        }
    }
    
    function set($campo, $valor, $paramType=LHDB::PARAM_STR){
        array_push($this->camposSet, $campo);
        array_push($this->valoresSet, $valor);
        array_push($this->tiposSet, $paramType);
    }
    
    function getInsertSql($insertOpt=""){
        $vals = array();
        foreach(array_keys($this->valoresSet) as $key){
            $vals[$key] = ":valoresSet$key";
        }
        
        $sql = "INSERT $insertOpt INTO $this->table (" . implode(",", $this->camposSet) . ")"
        . " VALUES (" . implode(",", $vals) . ")";
        
        return $sql;
    }
    
    function getUpdateSql(){
        $vals = array();
        $sql = "UPDATE $this->table SET ";
        $count = 0;
        foreach($this->camposSet as $key => $campo){
            if($count++ > 0) { $sql.=","; }
            $sql .= " $campo=:valoresSet$key";
        }
        
        $sql .= " WHERE " . $this->conditions["where"];
        return $sql;
    }
    
    function bindInsertUpdateParameters($stm){
        if(count($this->valoresSet)>0){
            foreach($this->valoresSet as $key => $valor){
                $stm->bindValue(":valoresSet$key", $valor, $this->tiposSet[$key]);
            }
        }
    }
    
    function insert(){
        $stm = $this->db->prepare($this->getInsertSql());
        $this->bindInsertUpdateParameters($stm);
        $stm->debugDumpParams();
        return $stm->execute();
    }
    
    function update(){
        $stm = $this->db->prepare($this->getUpdateSql());
        $this->bindInsertUpdateParameters($stm);
        $this->bindQueryParameters($stm);
        return $stm->execute();
    }
    
    function lastInsertId($name=null){
        return $this->db->lastInsertId($name);
    }
}

