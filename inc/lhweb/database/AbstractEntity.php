<?php
namespace lhweb\database;

use Exception;
use JsonSerializable;
/**
 * Description of AbstractEntity
 *
 * @author loki
 */
abstract class AbstractEntity implements JsonSerializable {
    public static $primaryKey = "id";
    public static $primaryKeyTipo = LHDB::PARAM_INT;
    public static $table = null;
    public static $processarJoins = true;
    
    /**
     *
     * @var AbstractEntity 
     */
    public static $joins = [];
    
    /**
     *
     * @var AbstractEntity 
     */
    public static $leftOuterJoins = [];
    
    public function __construct() {
    }
    
    /**
     *
     * @var array
     * Variável utilizada para configurar o mapeamento entre os campos da classe
     * e o nome das colunas no banco de dados. por ex:
     * protected $camposMap = array(
     *     "campoDaClasse" => "ColunaDoBanco"
     * );
     * Ao fazer um select, será utilizado na SQL o ColunaDoBanco, mas quando o 
     * resultado retornar será armazenado na propriedade campoDaClasse do objeto.
     * 
     */
    protected static $camposMap = array();
    protected static $camposReadOnly = array();
    protected static $camposBlockUpdate = array();
    protected static $camposBlockInsert = array();
    protected static $camposNaoSerializar = array();
    
    /**
     *
     * @var array
     * Tipos dos campos, deve ser declarado no formato:
     * nomeDoCampo => LHDB::PARAM_STR
     */
    protected static $tipos = array();
    private $__lh_edit_clone;
    
    
    public static function getCampos(){
        return static::$camposMap;
    }
    
    /**
     * Cria uma cópia da classe atual para comprar os valores e alterar
     * somente o necessário em caso de chamada de update.
     */
    public function editMode(){
        $c = static::class;
        $this->__lh_edit_clone = new $c();
        
        foreach($this as $key => $val) {
            if($key == "__lh_edit_clone") {
                continue;
            }
            
            $this->__lh_edit_clone->$key = $val;
        }
    }
    
    /**
     * Retorno um array com os campos a serem selecionados do banco de dados.
     * Adicionando o nome da tabela caso passado em $tablename no formato
     * NomeDaTabela.NomeDoCampo
     * e adicionando um prefixo ao nome do campo caso passado em prefixo.
     * 
     * @param string $tablename
     * @param strnig $prefix
     * @return string[]
     */
    public static function getCamposQuery($tablename=null, $prefix=null){
        $camposMap = array();
        
        $classe = static::class;
        $entidade = new $classe();
        
        // Percorre o array de campos, obtendo os nomes corretos.
        foreach($entidade as $key => $val){
            if(strpos($key, "__lh")===0) continue; // pula os campos de metadados.
            
            $campo = $tablename?$tablename . ".":"";
            if($prefix){
                $tmp = static::getNomeCampo($key);
                $campo .= $tmp . " as " . $prefix . "_" . $tmp;
            } else {
                $campo .= static::getNomeCampo($key);
            }

            array_push($camposMap, $campo);
        }

        return $camposMap;
    }
    
    public static function getJoinFieldName($fk){
        if(strpos($fk, ".")===false){ // JOIN DIRETO NA PROPRIA TABELA.
            return static::$table . "." . static::getNomeCampo($fk);
        } else {
            list($fkattr, $fkfield) = explode(".", $fk);
            
            $join_count = 0;
            foreach(static::$joins as $cj => $join){
                list($fk2, $attr) = $join;
                if($fkattr==$attr){
                    return $cj::$table . "_" . $join_count . "." . $cj::getNomeCampo($fkfield);
                }
                
                $join_count++;
            }
            
            foreach(static::$leftOuterJoins as $cj => $join){
                list($fk2, $attr) = $join;
                if($fkattr==$attr){
                    return $cj::$table . "_" . $join_count . "." . $cj::getNomeCampo($fkfield);
                }
                
                $join_count++;
            }
            
            return static::$table . "." . static::getNomeCampo($fk);
        }
    }
    
    /**
     * 
     * @return GenericQuery
     */
    public static function getBasicMoveQuery(){
        if(static::$table===null){
            $class = explode("\\",strtolower(static::class));
            static::$table = str_replace("entity", "", strtolower($class[count($class)-1]));
        }
        
        $q = LHDB::getConnection()->query(static::$table);
        $q->campos(static::getCamposQuery(static::$table));
        
        $join_count = 0;
        foreach(static::$joins as $cj => $join){
            list($fk, $attr) = $join;
            $jointable_name = $cj::$table . "_" . $join_count;
            
            $joinfield_name = static::getJoinFieldName($fk);
            $q->join($cj::$table . " AS " . $jointable_name , $jointable_name . "." . $cj::getPkName() . "=" . $joinfield_name);
            
            foreach($cj::getCamposQuery($jointable_name, $jointable_name) as $c){
                $q->addCampo($c);
            }
            
            $join_count++;
        }
        
        foreach(static::$leftOuterJoins as $cj => $join){
            list($fk, $attr) = $join;
            $original_table_name = $cj::$table; 
            $jointable_name = $cj::$table . "_" . $join_count;
            $q->leftOuterJoin($cj::$table . " AS " . $jointable_name , $jointable_name . "." . $cj::getPkName() . "=" . static::$table . "." . static::getNomeCampo($fk));
            
            foreach($cj::getCamposQuery($jointable_name, $jointable_name) as $c){
                $q->addCampo($c);
            }
            
            $cj::$table = $original_table_name;
            $join_count++;
        }
        
        return $q;
    }
    
    public static function getPkName(){
        return static::getNomeCampo(static::$primaryKey);
    }
    
    public static function getPkAttribute(){
        foreach(static::$camposMap as $key => $val) {
            if($val === static::$primaryKey) {
                return $key?$key:$val;
            }
        }
        
        return static::$primaryKey;
    }
    
    /**
     * 
     * Deve retornar o nome do campo a ser consultado no banco de dados, levando em
     * conta o nome da variável do join que pode estar adicionado exemplo, uma
     * tabela Pessoa te um join com a tabela de Endereços, 
     * posso estar procurando pelo nome do campo endereco.cep.
     * No caso devo retornar o nome da tabela Endereços obtido com base nos joins
     * e o nome da coluna Cep ( Obtido a partir da Entity Class Endereços 
     * 
     * @param String $campo
     * @param boolean $prependTableName
     * @return String
     */
    public static function getNomeCampo($campo){
        if(strpos($campo, ".")!==FALSE){
            list($tabela,$campo) = explode(".", $campo);
            
            // Procurando nos Joins
            $join_count = 0;
            foreach(static::$joins as $cj => $val) {
                list($join_campo, $join_variable) = $val;
                
                $original_table_name = $cj::$table;
                $cj::$table     = $cj::$table . "_" . $join_count;
                //$cj::$processarJoins = false;
                
                if($join_variable==$tabela) {
                    return $cj::getNomeCampo($campo);
                }
                $join_count++;
                $cj::$table = $original_table_name;
                // $cj::$processarJoins = true;
            }
            
            foreach(static::$leftOuterJoins as $cj => $val) {
                list($join_campo, $join_variable) = $val;
                
                $original_table_name = $cj::$table;
                $cj::$table     = $cj::$table . "_" . $join_count;
                // $cj::$processarJoins = false;
                
                if($join_variable==$tabela) {
                    return $cj::getNomeCampo($campo);
                }
                $join_count++;
                $cj::$table = $original_table_name;
                // $cj::$processarJoins = true;
            }
        } else {
            return array_key_exists($campo, static::$camposMap)?static::$camposMap[$campo]:$campo;
        }
    }
    
    public static function getTipoCampo($campo){
        return array_key_exists($campo, static::$tipos)?static::$tipos[$campo]:LHDB::PARAM_STR;
    }
    
    /**
     * 
     * @param type $rs
     * @returns AbstractEntity
     */
    public static function makeFromRs($rs, $prefix="") {
        if(!$rs) {
            return NULL;
        }
        
        $c = static::class;
        $o = new $c();
        
        foreach($o as $key => $val){
            $campoDoBanco = $prefix . static::getNomeCampo($key);
            
            if(is_array($rs) && array_key_exists($campoDoBanco, $rs)){
                $o->$key = $rs[$campoDoBanco];
            } else if(is_object($rs) && property_exists($campoDoBanco, $campoDoBanco)){
                $o->$key = $rs->$campoDoBanco;
            }
        }
        
        if(static::$processarJoins){
            $join_count = 0;
            foreach(static::$joins as $cj => $join){
                list($fk, $attr) = $join;
                $original_table_name = $cj::$table;
                $cj::$table     = $cj::$table . "_" . $join_count;
                $cj::$processarJoins = false;
                
                $o->$attr = $cj::makeFromRs($rs, $cj::$table . "_");
                $join_count++;
                $cj::$table = $original_table_name;
                $cj::$processarJoins = true;
            }

            foreach(static::$leftOuterJoins as $cj => $join){
                list($fk, $attr) = $join;
                $original_table_name = $cj::$table;
                $cj::$table     = $cj::$table . "_" . $join_count;
                $cj::$processarJoins = false;
                $o->$attr = $cj::makeFromRs($rs, $cj::$table . "_");
                $join_count++;
                $cj::$table = $original_table_name;
                $cj::$processarJoins = true;
            }
        }
        
        return $o;
    }
    
    /**
     * 
     * @param int $pk
     * @return AbstractEntity
     */
    public static function getByPK($pk){
        $rs = static::getBasicMoveQuery()
                ->andWhere(static::$table . "." . static::getPkName())->equals($pk, static::$primaryKeyTipo)
                ->getSingle();
        
        return static::makeFromRs($rs);
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public static function primeiro(){
        $q = static::getBasicMoveQuery()->orderby(static::$table . "." . static::getPkName());
        
        try {
            $rs = $q->getSingle();
            return static::makeFromRs($rs);
        } catch(Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public static function ultimo(){
        $q = static::getBasicMoveQuery()->orderby(static::$table . "." . static::getPkName(),"DESC");
        try {
            $rs = $q->getSingle();
            return static::makeFromRs($rs);
        } catch(Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * 
     * @param type $pk
     * @return AbstractEntity
     */
    public static function proximo($pk){
        $q = static::getBasicMoveQuery()
                ->andWhere(static::$table . "." . static::getPkName())->maiorQue($pk, static::$primaryKeyTipo)
                ->orderBy(static::$table . "." . static::getPkName(),"ASC");
        
        try {
            $rs = $q->getSingle();
            return static::makeFromRs($rs);
        }  catch(Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * 
     * @param type $pk
     * @return AbstractEntity
     */
    public static function anterior($pk){
        $q = static::getBasicMoveQuery()
                ->andWhere(static::$table . "." . static::getPkName())->menorQue($pk, static::$primaryKeyTipo)
                ->orderBy(static::$table . "." . static::getPkName(),"DESC");
        try {
            $rs = $q->getSingle();
            return static::makeFromRs($rs);
        }  catch(Exception $ex) {
            throw $ex;
        }
    }
    
    
    /**
     * 
     * @param int $pk
     * @return AbstractEntity
     */
    public static function listar($limit=0, $offset=0){
        $q = static::getBasicMoveQuery();
        
        if($limit) {
            $q->limit($limit);
        }
        
        if($offset) {
            $q->offset($offset);
        }
        
        try {
            return new EntityArray($q->getList(), static::class);
        }  catch(Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * 
     * @param string $campo
     * @param type $txt
     * @return array
     */
    public static function getProcurarQuery($campo, $txt, $modo="like") {
        $q = static::getBasicMoveQuery();
        
        if(!method_exists($q, $modo)){
            throw new Exception("Modo de Procura Inválido: $modo");
        }
        
        $q->andWhere(static::getNomeCampo($campo))->$modo($txt, static::getTipoCampo($campo));
        
        return $q;
    }
    
    public static function getBy($campo, $txt, $modo="like") {
        $q = static::getProcurarQuery($campo, $txt, $modo);
        
        try {
            return static::makeFromRs($q->getSingle());
        }  catch(Exception $ex) {
            throw $ex;
        }
    }
    
    public static function listarPor($campo, $txt, $modo="like") {
        $q = static::getProcurarQuery($campo, $txt, $modo);
        
        try {
            return new EntityArray($q->getList(),static::class);
        }  catch(Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function insert(){
        $q = LHDB::getConnection()->query(static::$table);
        foreach($this as $key => $val) {
            if(!isset($val)){
                continue;
            } else if($key == "__lh_edit_clone") {
                continue;
            } else if(in_array($key, static::$camposBlockInsert) || in_array($key, static::$camposReadOnly)){
                continue;
            }
            
            $tipo = static::getTipoCampo($key);
            $q->set(static::getNomeCampo($key), $val, $tipo);
        }
        
        try {
            return $q->insert();
            // $primaryKey  = static::$primaryKey;
            // $this->$primaryKey = $q->lastInsertId();
        }  catch(Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function update(){
        $count = 0;
        
        // Cria um modelo para evitar campos adicionados posteriomente quebrem a sql.
        $c = static::class;
        $mdl = new $c();
        
        $q = LHDB::getConnection()->query(static::$table);
        foreach($mdl as $key => $val) {
            $val = $this->$key;
            if($key==static::$primaryKey){ // não devo atualizar a chave primaria
                continue;
            } else if($this->__lh_edit_clone && $val == $this->__lh_edit_clone->$key) { // checando se o valor foi alterado
                continue;
            } else if($key == "__lh_edit_clone") {
                continue;
            } else if(in_array($key, static::$camposBlockUpdate) || in_array($key, static::$camposReadOnly)){
                continue;
            }
            
            $tipo = static::getTipoCampo($key);
            $q->set(static::getNomeCampo($key), $val, $tipo);
            $count++;
        }
        
        $pkName = static::$primaryKey;
        $q->andWhere(static::$table . "." . static::getPkName())->equals($this->$pkName, static::$primaryKeyTipo);
        
        if($count>0){
            try {
                return $q->update();
            }  catch(Exception $ex) {
                throw $ex;
            }
        }
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return int
     */
    public function delete(){
        $pkName = static::$primaryKey;
        $q = LHDB::getConnection()->query(static::$table);
        $q->andWhere(static::getPkName())->equals($this->$pkName, static::$primaryKeyTipo);
        
        try {
            return $q->delete();
        }  catch(Exception $ex) {
            throw $ex;
        }
    }
    
    public function __toString() {
        $pk = static::$primaryKey;
        return static::class . "[" . $this->$pk . "]";
    }
    
    /*
     * @return int
     */
    public function count(){
        $rs = static::getBasicMoveQuery()->campos(array("COUNT(" . static::$table . "." . static::getPkName() . ") as total"))->getSingle();
        return $rs["total"];
    }

    public function jsonSerialize (){
        $ret = array();
        foreach($this as $key => $val){ 
            if(strpos($key, "__lh")===0 || in_array($key, static::$camposNaoSerializar)){
                continue;
            }
            
            if($val instanceof EntityArray){ // Armazena em formato convertível para json, no caso um array.
                $ret[$key] = $val->jsonSerialize();
            } else {
                $ret[$key] = $val;
            }
        }
        
        if(method_exists($this, "__toString")){
            $ret["toString"] = $this->__toString();
        } else {
            $ret["toString"] = static::class;
        }
        return $ret;
    }
}
