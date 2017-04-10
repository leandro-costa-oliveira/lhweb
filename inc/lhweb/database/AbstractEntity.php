<?php
namespace lhweb\database;
/**
 * Description of AbstractEntity
 *
 * @author loki
 */
abstract class AbstractEntity {
    protected static $primaryKey = "id";
    protected static $primaryKeyTipo = LHDB::PARAM_INT;
    protected static $table = null;
    private $editClone;
    
    
    /**
     * Cria uma cópia da classe atual para comprar os valores e alterar
     * somente o necessário em caso de chamada de update.
     */
    public function editMode(){
        $c = static::class;
        $this->editClone = new $c();
        
        echo "## CREATING EDIT CLONE [$c]\n";
        foreach($this as $key => $val) {
            if($key == "editClone") {
                echo "## EDIT CLONE CONTINUING\n";
                continue;
            }
            
            $this->editClone->$key = $val;
        }
    }
    
    /**
     * 
     * @return string
     */
    public static function getTableName(){
        if(static::$table === null){
            return str_replace("entity","",strtolower(static::class));
        } else {
            return static::$table;
        }
    }
    
    /**
     * 
     * @return string
     */
    public static function getCamposSelect(){
        return static::getTableName() . ".*";
    }
    
    /**
     * 
     * @return GenericQuerys
     */
    public static function getBasicMoveQuery(){
        return LHDB::getConnection()
                ->query(static::getTableName())
                ->campos(array(static::getTableName().".*"));
    }
    
    public function getPkName(){
        return static::getTableName() . "." . static::$primaryKey;
    }
    
    /**
     * 
     * @param type $rs
     * @returns AbstractEntity
     */
    public static function makeFromRs($rs) {
        if(!$rs) {
            return NULL;
        }
        
        $c = static::class;
        $o = new $c();
        foreach($o as $key => $val){
            if(is_array($rs) && array_key_exists($key, $rs)){
                $o->$key = $rs[$key];
            } else if(is_object($rs) && property_exists($rs, $key)){
                $o->$key = $rs->$key;
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
                ->andWhere(static::getPkName())->equals($pk, static::$primaryKeyTipo)
                ->getSingle(static::class);
        
        return static::makeFromRs($rs);
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public static function primeiro(){
        $q = static::getBasicMoveQuery()->orderby(static::getPkName());
        $rs = $q->getSingle(static::class);
        echo $q->getQuerySql() . "\n";
        return static::makeFromRs($rs);
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public static function ultimo(){
        $rs = static::getBasicMoveQuery()
                ->orderby(static::getPkName(),"DESC")
                ->getSingle(static::class);
        return static::makeFromRs($rs);
    }
    
    /**
     * 
     * @param type $pk
     * @return AbstractEntity
     */
    public static function proximo($pk){
        $rs = static::getBasicMoveQuery()
                ->andWhere(static::getPkName())->maiorQue($pk, static::$primaryKeyTipo)
                ->getSingle(static::class);
        return static::makeFromRs($rs);
    }
    
    /**
     * 
     * @param type $pk
     * @return AbstractEntity
     */
    public static function anterior($pk){
        $rs = static::getBasicMoveQuery()
                ->where(static::getPkName())->menorQue($pk, static::$primaryKeyTipo)
                ->orderBy(static::getPkName(),"DESC")
                ->getSingle(static::class);
        return static::makeFromRs($rs);
    }
    
    
    /**
     * 
     * @param int $pk
     * @return AbstractEntity
     */
    public static function listar(){
        return new EntityArray(static::getBasicMoveQuery()->getList(static::class), static::class);
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
        
        $q->andWhere($campo)->$modo($txt);
        
        return $q;
    }
    
    public static function procurar($campo, $txt, $modo="like") {
        $q = static::getProcurarQuery($campo, $txt, $modo);
        return new EntityArray($q->getList(static::class),static::class);
    }
    
    public static function getBy($campo, $txt, $modo="like") {
        $q = static::getProcurarQuery($campo, $txt, $modo);
        return $q->getSingle($c);
    }
    
    public static function listarPor($campo, $txt, $modo="like") {
        $q = static::getProcurarQuery($campo, $txt, $modo);
        return $q->getList($c);
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function insert(){
        $q = LHDB::getConnection()->query(static::getTableName());
        foreach($this as $key => $val) {
            if(!$val){
                continue;
            } else if($key == "editClone") {
                continue;
            }
            
            $campoTipo = $key."Tipo";
            if(property_exists(static::class, $campoTipo)){
                $tipo = static::$$campoTipo;
            } else {
                $tipo = LHDB::PARAM_STR;
            }
            $q->set($key, $val, $tipo);
        }
        
        $q->insert();
        $primaryKey  = static::$primaryKey;
        $this->$primaryKey = $q->lastInsertId();
        return $this;
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function update(){
        $count = 0;
        $q = LHDB::getConnection()->query(static::getTableName());
        foreach($this as $key => $val) {
            if($key==static::$primaryKey){
                continue;
            } else if($val == $this->editClone->$key) {
                continue;
            } else if($key == "editClone") {
                continue;
            }
            
            $campoTipo = $key."Tipo";
            if(property_exists($c, $campoTipo)){
                $tipo = static::$$campoTipo;
            } else {
                $tipo = LHDB::PARAM_STR;
            }
            
            $q->set($key, $val, $tipo);
            $count++;
        }
        $pkName = static::$primaryKey;
        $q->andWhere($this->getPkName())
                ->equals($this->$pkName, static::$primaryKeyTipo);
        
        echo "UPDATE SQL:" . $q->getUpdateSql(). "\n";
        if($count>0){
            $q->update();
        }
        return;
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function salvar(){
        $pkName = static::$primaryKey;
        if(property_exists($this, $pkName) && !empty($this->$pkName)){
            return $this->update();
        } else {
            return $this->insert();
        }
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return int
     */
    public function delete(){
        $q = LHDB::getConnection()->query(static::getTableName());
        $q->andWhere(static::$primaryKey)->equals($this->primaryKey, $this->primaryKeyTipo);
        return $q->delete();
    }
}
