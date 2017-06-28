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
    protected $campos = array("*");
    protected $camposSet= array();
    protected $union  = array();
    protected $valores = array();
    protected $valoresSet = array();
    protected $tiposSet = array();
    protected $join    = array();
    protected $limit   = null;
    protected $offset  = null;
    protected $orderBy  = null;
    protected $groupBy = null;
    protected $conditions = array(
        "where" => "",
        "having" => "",
    );
    protected $condition = "where";
    protected $addAndOr = array(
        "where" => false,
        "having" => false,
    );
    
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
        if($this->conditions[$this->condition] && $this->addAndOr[$this->condition]){
            $this->where(" AND ");
            $this->addAndOr[$this->condition] = false;
        }
        
        return $this->where("$valor");
    }
    
    public function orWhere($valor){
        if($this->conditions[$this->condition] && $this->addAndOr[$this->condition]){
            $this->where(" OR ");
            $this->addAndOr[$this->condition] = false;
        } 
        
        return $this->where("$valor");
    }
    
    public function having($valor){
        $this->condition = "having";
        $this->conditions[$this->condition] .= $$valor;
        
        return $this;
    }
    
    public function andHaving($valor){
        if($this->conditions[$this->condition] && $this->addAndOr[$this->condition]){
            $this->having(" AND ");
            $this->addAndOr[$this->condition] = false;
        }
        
        return $this->having("$valor");
    }
    
    public function orHaving($valor){
        if($this->conditions[$this->condition] && $this->addAndOr[$this->condition]){
            $this->having(" OR ");
            $this->addAndOr[$this->condition] = false;
        }
        
        $this->having("$valor");
    }
    
    public function basicCondition($op, $txt, $paramType) {
        $this->conditions[$this->condition] .= " $op :valores" . count($this->valores). " ";
        $this->addAndOr[$this->condition] = true;
        array_push($this->valores, array("v" => $txt, "t" => $paramType));
        return $this;
    }
    
    /**
     * 
     * @param GenericQuery $sq
     */
    public function inSubquery($sq) {
        $sql = $sq->getQuerySql();
        $sql = str_replace(":valores",":valores_sb",$sql);
        
        foreach($sq->valores as $key => $v) {
            $idx = count($this->valores);
            array_push($this->valores, $v);
            $sql = str_replace(":valores_sb$key",":valores$idx",$sql);
        }
        
        $this->conditions[$this->condition] .= " IN (". $sql . ")";
        $this->addAndOr[$this->condition] = true;
        return $this;
    }
    
    public function in($dados, $paramType=LHDB::PARAM_STR) {
        if($dados instanceof GenericQuery) {
            return $this->inSubquery($dados);
        }
        
        // Evitando erro na consulta caso receba um array vazio.
        if(count($dados) <= 0){
            return $this->equals("",$paramType);
        }
        
        $this->addAndOr[$this->condition]    = true;
        $this->conditions[$this->condition] .= " IN (";
        
        $count = 0;
        foreach($dados as $val){
            if($count++ > 0){
                $this->conditions[$this->condition] .= ",";
            }
            $this->conditions[$this->condition] .= ":valores" . count($this->valores);
            array_push($this->valores, array("v" => $val, "t" => $paramType));
        }
        $this->conditions[$this->condition] .= ") ";
        return $this;
    }
    
    public function isNull() {
        $this->conditions[$this->condition] .= " IS NULL";
        return $this;
    }
    
    public function isNotNull() {
        $this->conditions[$this->condition] .= " IS NOT NULL";
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
    
    public function join($table, $condition) {
        array_push($this->join, " JOIN $table ON $condition");
    }
    
    public function leftOuterJoin($table, $condition) {
        array_push($this->join, " LEFT OUTER JOIN $table ON $condition");
    }
    
    public function orderBy($txt, $dir="ASC"){
        $this->orderBy .= "$txt $dir";
        return $this;
    }
    
    public function groupBy($txt){
        $this->groupBy .= $txt;
        return $this;
    }
    
    public function limit($l){
        $this->limit = $l;
        return $this;
    }
    
    public function offset($o){
        $this->offset = $o;
        return $this;
    }
    
    function getQuerySql(){
        $sql = "SELECT " . implode(",", $this->campos) . " FROM $this->table";
        
        foreach($this->join as $j) {
            $sql .= " $j ";        
        }
        
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
    
    function getDeleteSql(){
        $vals = array();
        $sql = "DELETE FROM $this->table WHERE " . $this->conditions["where"];
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
        return $stm->execute();
    }
    
    function update(){
        $stm = $this->db->prepare($this->getUpdateSql());
        $this->bindInsertUpdateParameters($stm);
        $this->bindQueryParameters($stm);
        return $stm->execute();
    }
    
    function delete(){
        $stm = $this->db->prepare($this->getDeleteSql());
        $this->bindQueryParameters($stm);
        return $stm->execute();
    }
    
    function lastInsertId($name=null){
        return $this->db->lastInsertId($name);
    }
}

