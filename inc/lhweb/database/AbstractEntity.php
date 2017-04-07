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
        $c = get_called_class();
        $this->editClone = new $c;
        
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
        $c = get_called_class();
        if($c::$table == null){
            return str_replace("entity","",strtolower($c));
        } else {
            return $c::$table;
        }
    }
    
    /**
     * 
     * @return GenericQuerys
     */
    public static function getBasicMoveQuery(){
        $c = get_called_class();
        return LHDB::getConnection()->query($c::getTableName());
    }
    
    public function getPkName(){
        $c  = get_called_class();
        return $c::getTableName() . "." . $c::$primaryKey;
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
        
        $c = get_called_class();
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
        $c  = get_called_class();
        $rs = $c::getBasicMoveQuery()
                ->andWhere($c::getPkName())->equals($pk, $c::$primaryKeyTipo)
                ->getSingle($c);
        
        return $c::makeFromRs($rs);
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public static function primeiro(){
        $c  = get_called_class();
        $rs = $c::getBasicMoveQuery()
                ->orderby($c::getPkName())
                ->getSingle($c);
        return $c::makeFromRs($rs);
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public static function ultimo(){
        $c  = get_called_class();
        $rs = $c::getBasicMoveQuery()
                ->orderby($c::getPkName(),"DESC")
                ->getSingle($c);
        return $c::makeFromRs($rs);
    }
    
    /**
     * 
     * @param type $pk
     * @return AbstractEntity
     */
    public static function proximo($pk){
        $c  = get_called_class();
        $rs = $c::getBasicMoveQuery()
                ->andWhere($c::getPkName())->maiorQue($pk, $c::$primaryKeyTipo)
                ->getSingle($c);
        return $c::makeFromRs($rs);
    }
    
    /**
     * 
     * @param type $pk
     * @return AbstractEntity
     */
    public static function anterior($pk){
        $c  = get_called_class();
        $rs = $c::getBasicMoveQuery()
                ->where($c::getPkName())->menorQue($pk, $c::$primaryKeyTipo)
                ->orderBy($c::getPkName(),"DESC")
                ->getSingle($c);
        return $c::makeFromRs($rs);
    }
    
    
    /**
     * 
     * @param int $pk
     * @return AbstractEntity
     */
    public static function listar(){
        $c  = get_called_class();
        return new EntityArray($c::getBasicMoveQuery()->getList($c), $c);
    }
    
    /**
     * 
     * @param string $campo
     * @param type $txt
     * @return array
     */
    public static function getProcurarQuery($campo, $txt, $modo="like") {
        $c = get_called_class();
        $q = $c::getBasicMoveQuery();
        
        if(!method_exists($q, $modo)){
            throw new Exception("Modo de Procura Inválido: $modo");
        }
        
        $q->andWhere($campo)->$modo($txt);
        
        return $q;
    }
    
    public static function procurar($campo, $txt, $modo="like") {
        $c = get_called_class();
        $q = $c::getProcurarQuery($campo, $txt, $modo);
        return new EntityArray($q->getList($c),$c);
    }
    
    public static function getBy($campo, $txt, $modo="like") {
        $c = get_called_class();
        $q = $c::getProcurarQuery($campo, $txt, $modo);
        return $q->getSingle($c);
    }
    
    public static function listarPor($campo, $txt, $modo="like") {
        $c = get_called_class();
        $q = $c::getProcurarQuery($campo, $txt, $modo);
        return $q->getList($c);
    }
    
    /**
     * 
     * @param AbstractEntity $obj
     * @return AbstractEntity
     */
    public function insert(){
        $c = get_called_class();
        $q = LHDB::getConnection()->query($c::getTableName());
        foreach($this as $key => $val) {
            if(!$val){
                continue;
            } else if($key == "editClone") {
                continue;
            }
            
            $campoTipo = $key."Tipo";
            if(property_exists($c, $campoTipo)){
                $tipo = $c::$$campoTipo;
            } else {
                $tipo = LHDB::PARAM_STR;
            }
            $q->set($key, $val, $tipo);
        }
        
        $q->insert();
        $primaryKey  = $c::$primaryKey;
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
        $c = get_called_class();
        $q = LHDB::getConnection()->query($c::getTableName());
        foreach($this as $key => $val) {
            if($key==$c::$primaryKey){
                continue;
            } else if($val == $this->editClone->$key) {
                continue;
            } else if($key == "editClone") {
                continue;
            }
            
            $campoTipo = $key."Tipo";
            if(property_exists($c, $campoTipo)){
                $tipo = $c::$$campoTipo;
            } else {
                $tipo = LHDB::PARAM_STR;
            }
            
            $q->set($key, $val, $tipo);
            $count++;
        }
        $pkName = $c::$primaryKey;
        $q->andWhere($this->getPkName())
                ->equals($this->$pkName, $c::$primaryKeyTipo);
        
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
        $c  = get_called_class();
        $pkName = $c::$primaryKey;
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
        $c = get_called_class();
        $q = LHDB::getConnection()->query($c::getTableName());
        $q->andWhere($c::$primaryKey)->equals($this->primaryKey, $this->primaryKeyTipo);
        return $q->delete();
    }
}
