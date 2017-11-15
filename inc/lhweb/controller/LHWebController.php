<?php
namespace lhweb\controller;

use lhweb\database\GenericQuery;
use lhweb\database\LHDB;
use lhweb\database\LHEntityArray;
use lhweb\database\LHWebEntity;
use lhweb\exceptions\RegistroNaoEncontradoException;

class LHWebController {
    /**
     *
     * @var LHWebEntity
     */
    protected $class_entidade = null;
    
    protected $tabela = null;
    
    /**
     *
     * @var LHDB
     */
    protected $lhdb;
    
    public function __construct($class_entidade) {
        $this->class_entidade = $class_entidade;
        $this->lhdb = LHDB::getConnection();
        
        if($class_entidade::$tabela) {
            $this->tabela = $class_entidade::$tabela;
        } else { // Gerando Nomeclatura Padrão da Tabela.
            $this->tabela = static::getNomePadraoTabela($class_entidade);
        }
    }
    
    public static function getNomePadraoTabela($class_entidade){
        $class = explode("\\",strtolower($class_entidade));
        return str_replace("entity", "", strtolower($class[count($class)-1]));
    }
    
    /**
     * 
     * @return string
     */
    public function getNomeChavePrimaria($prependNomeTabela=false){
        $c = $this->class_entidade;
        return ($prependNomeTabela?$this->tabela.".":"") . $c::$nomeChavePrimaria;
    }
    
    /**
     * 
     * @return int
     */
    public function getTipoChavePrimaria(){
        $c = $this->class_entidade;
        return $c::$tipoChavePrimaria;
    }
    
    /**
     * 
     * @return GenericQuery
     */
    protected function getBasicMoveQuery(){
        $q = $this->lhdb->query($this->tabela);
        
        // Processar Joins
        // Processar Left Outer Joins
        
        return $q;
    }
    
    /**
     * 
     * @param string $campo
     * @param boolean $prependNomeTabela
     * @return string
     * Retornar o nome do campo, levando em conta o mapeamento de colunas 
     * para o banco de dados.
     */
    public function getNomeCampo($campo, $prependNomeTabela=false) {
        $classe_entidade = $this->class_entidade;
        
        $nomecampo = $prependNomeTabela?$this->tabela . ".":"";
        if(array_key_exists($campo, $classe_entidade::$mapaCampos)){
            $nomecampo .= $classe_entidade::$mapaCampos[$campo];
        } else {
            $nomecampo .= $campo;
        }
        
        return $nomecampo;
    }
    
    /**
     * 
     * @param type $c
     * @return string
     * Retornar o nome do campo, levando em conta o mapeamento de colunas 
     * para o banco de dados.
     */
    public function getTipoCampo($c) {
        $classe_entidade = $this->class_entidade;
        if(array_key_exists($c, $classe_entidade::$mapaTipos)){
            $classe_entidade::$mapaTipos[$c];
        } else {
            return LHDB::PARAM_STR;
        }
    }
    
    /**
     * 
     * @param type $rs
     * @return LHWebEntity
     * Recebe um ResultSet com um registro de preenche o objeto Entity.
     */
    public function getEntityFromRS($rs, $prefix="") {
        $class_entidade = $this->class_entidade;
        $obj = new $class_entidade();
        
        // Percorre os atributos de $obj e preencher do $rs.
        foreach($obj as $key => $val){
            $coluna = $prefix . $this->getNomeCampo($key);
            
            if(is_array($rs)){
                $obj->$key = array_key_exists($coluna, $rs)?$rs[$coluna]:null;
            } else if(is_object($rs)){
                $obj->$key = property_exists($coluna, $coluna)?$rs->$coluna:null;
            }
        }
        
        // Processar Joins
        
        return $obj;
    }
    
    /**
     * 
     * @return LHWebEntity
     */
    public function primeiro(){
        $q = $this->getBasicMoveQuery()
                ->orderby($this->getNomeChavePrimaria(true), "ASC");
        return $this->getEntityFromRS($q->getSingle());
    }
    
    /**
     * 
     * @return LHWebEntity
     */
    public function ultimo(){
        $q = $this->getBasicMoveQuery()
                ->orderby($this->getNomeChavePrimaria(true), "DESC");
        return $this->getEntityFromRS($q->getSingle());
    }
    
    /**
     * 
     * @return LHWebEntity
     */
    public function anterior($chave_primaria){
        $q = $this->getBasicMoveQuery()
                ->andWhere($this->getNomeChavePrimaria(true))->maiorQue($chave_primaria, $this->getTipoChavePrimaria())
                ->orderby($this->getNomeChavePrimaria(true), "DESC");
        $this->getEntityFromRS($q->getSingle());
    }
    
    /**
     * 
     * @return LHWebEntity
     */
    public function proximo($chave_primaria){
        $q = $this->getBasicMoveQuery()
                ->andWhere($this->getNomeChavePrimaria(true))->maiorQue($chave_primaria, $this->getTipoChavePrimaria())
                ->orderby($this->getNomeChavePrimaria(true), "ASC");
        $this->getEntityFromRS($q->getSingle());
    }
    
    /**
     * 
     * @return LHWebEntity
     */
    public function getByPK($chave_primaria){
        $q = $this->getBasicMoveQuery()
                ->andWhere($this->getNomeChavePrimaria(true))->equals($chave_primaria, $this->getTipoChavePrimaria());
        $this->getEntityFromRS($q->getSingle());
    }
    
    
    /**
     * 
     * @param string $campo
     * @param type $txt
     * @return array
     */
    public function getProcurarQuery($campo, $txt, $modo="like") {
        $q = $this->getBasicMoveQuery();
        
        if(!method_exists($q, $modo)){
            throw new Exception("Modo de Procura Inválido: $modo");
        }
        
        $q->andWhere($this->getNomeCampo($campo,true))->$modo($txt, $this->getTipoCampo($campo));
        
        return $q;
    }
    
    public function getBy($campo, $txt, $modo="like") {
        $q = $this->getProcurarQuery($campo, $txt, $modo);
        return $this->getEntityFromRS($q->getSingle());
    }
    
    public static function listarPor($campo, $txt, $modo="like") {
        $q = $this->getProcurarQuery($campo, $txt, $modo);
        return new LHEntityArray($q->getList(), $this);
    }
    
    /**
     * 
     * @return int
     * @throws RegistroNaoEncontradoException
     */
    public function apagar($chave_primaria){
        $obj = $this->getByPK($chave_primaria);
        
        if($obj){
            $this->preApagar($obj);
            $this->getBasicMoveQuery()
                    ->andWhere($this->getNomeChavePrimaria(true))->equals($chave_primaria, $this->getTipoChavePrimaria())
                    ->delete();
            $this->posApagar($obj);
            return $this->anterior($chave_primaria);
        } else {
            throw new RegistroNaoEncontradoException("PK:".htmlspecialchars($chave_primaria));
        }
    }
    
    /**
     * 
     * @param LHWebEntity $obj
     */
    public function validar($obj){
        if($obj == null){
            throw new RegistroNaoEncontrado();
        }
    }
    
    
    /**
     * 
     * @param LHWebEntity $obj
     * @return LHWebEntity
     */
    public function salvar($obj){
        $this->validar($obj);        
        $this->preSalvar($obj);

        $pkName = $this->getNomeChavePrimaria();
        
        /**
         * Caso a chave primaria esteja definida, chama o metodo update e seus
         * respectivos eventos, do contrario, o metodo insert.
         */
        if(property_exists($obj, $pkName) && !empty($obj->$pkName)){
            $chave_primaria = $obj->$pkName;
            
            $this->preUpdate($obj);
            $this->update($obj);
            
            $obj2 = $this->getByPK($chave_primaria); // Obtem uma cópia atualizada do objetdo no banco de dados.
            $this->posUpdate($obj, $obj2);
        } else {
            $this->preInsert($obj);
            $chave_primaria = $this->insert($obj);
            
            $obj2 = $this->getByPK($chave_primaria);
            $this->posInsert($obj, $obj2);
        }
        $this->posSalvar($obj2);
            
        return $obj2;
    }
    
    /**
     * 
     * @param LHWebEntity $obj
     * @return int
     * Monta a SQL e Persiste o novo Objeto  no Banco
     */
    public function insert($obj) {
        $classe_entidade = $this->class_entidade;
        $q = $this->lhdb->query($this->tabela);
        
        foreach($obj as $key => $val) {
            if(!isset($val)
                || in_array($key, $classe_entidade::$camposNaoInserir) 
                || in_array($key, $classe_entidade::$camposSomenteLeitura)
            ){
                continue;
            }
            
            $q->set($this->getNomeCampo($key), $val, $this->getTipoCampo($key));
        }
        
        $q->insert();
        return $q->lastInsertId();
    }
    
    /**
     * 
     * @param LHWebEntity $obj
     * @return LHWebEntity
     */
    public function update($obj) {
        $classe_entidade = $this->class_entidade;
        $q = $this->lhdb->query($this->tabela);
        
        $nome_chave_primaria = $this->getNomeChavePrimaria();
        
        foreach($obj as $key => $val) {
            if(!isset($val)
                    || $key == $nome_chave_primaria
                    || in_array($key, $classe_entidade::$camposNaoAlterar) 
                    || in_array($key, $classe_entidade::$camposSomenteLeitura)
            ){
                continue;
            }
            
            $q->set($this->getNomeCampo($key), $val, $this->getTipoCampo($key));
        }
        
        $q->where($nome_chave_primaria)->equals($obj->$nome_chave_primaria);
        return $q->update();
    }
    
    /**
     * 
     * @param LHWebEntity $obj
     * @return LHWebEntity
     */
    public function preUpdate($obj){}
    
    /**
     * 
     * @param LHWebEntity $old
     * @param LHWebEntity $new
     */
    public function posUpdate($old, $new){}
    
    /**
     * 
     * @param LHWebEntity $obj
     * @return LHWebEntity
     */
    public function posInsert($old, $new){}
    
    /**
     * 
     * @param LHWebEntity $obj
     * @return LHWebEntity
     */
    public function preInsert($obj){}
    
    /**
     * 
     * @param LHWebEntity $obj
     * @return LHWebEntity
     */
    public function preSalvar($obj){}
    
    /**
     * 
     * @param LHWebEntity $obj
     * @return LHWebEntity
     */
    public function posSalvar($obj){}
    
    
    /**
     * 
     * @param LHWebEntity $obj
     */
    public function preApagar($obj){}
    
    /**
     * 
     * @param LHWebEntity $obj
     */
    public function posApagar($obj){}
    
    public function listar($limit=0, $offset=0){
        $q = $this->getBasicMoveQuery();
        
        if($limit) {
            $q->limit($limit);
        }
        
        if($offset) {
            $q->offset($offset);
        }
        
        try {
            return new LHEntityArray($q->getList(), $this);
        }  catch(Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * 
     * @param string $campo
     * @return string
     * Retorna o nome do campo a ser utilizado nas consultas de procura, levando 
     * em conta campos de classes associadas ( Joinned Tables ), 
     */
    function getNomeCampoProcura($campo) {
        $classe_entitdade = $this->class_entidade;
        if(strpos($campo, ".")!==false){ // PROCURAR NOS JOINS
            list($subCampo, $campo) = explode(".", $campo);
            
            $joinCount = 0;
            foreach($classe_entitdade::$joins as $classJoin => $det) {
                list($campoJoin, $varName) = $det;
                if($subCampo==$varName){
                    return $classJoin::getNomeCampo($campo, true, "_$joinCount");
                }
                $joinCount++;
            }
            
            foreach($classe_entitdade::$leftOuterJoins as $classJoin => $det) {
                list($campoJoin, $varName) = $det;
                if($subCampo==$varName){
                    return $classJoin::getNomeCampo($campo, true, "_$joinCount");
                }
                $joinCount++;
            }
        } else {
            // É UM CAMPO DA PROPRIA CLASSE
            // Retorna o nome do campo precedido da tabela.
            return $this->getNomeCampo($campo, true); 
        }
    }
    
    
    /**
     * @param string $campo
     * @param string $valor
     * @return LHWebEntity
     */
    public function procurar($campo, $valor, $limit=0, $offset=0){
        $obj = $this->class_entidade;
        if(is_array($campo)){
            $q = $this->getQueryProcurarCampoArray($obj, $campo, $valor);
        } else {
            $q = $this->getQueryProcurarCampoString($obj, $campo, $valor);
        }
        
        if($limit) { $q->limit($limit); }
        if($offset) { $q->offset($offset); }
        
        return new LHEntityArray($q->getList(), $obj);
    }
    
    function getQueryProcurarCampoString($campo, $valor){
        $q = $this->getBasicMoveQuery();
        $q->andWhere($this->getNomeCampoProcura($campo))->like($valor, $this->getTipoCampo($campo));
        return $q;
    }
    
    function getQueryProcurarCampoArray($campos, $valor){
        $q = $this->getBasicMoveQuery();
        
        $q->andWhere("(");
        foreach($campos as $campo){
            $q->orWhere($this->getNomeCampoProcura($campo))->like($valor, $this->getTipoCampo($campo));
        }
        $q->Where(")");
        
        return $q;
    }
    
    /*
     * @return int
     */
    public function count(){
        $rs = $this->getBasicMoveQuery()
                ->campos(array("COUNT(" . $this->getNomeChavePrimaria(true) . ") as total"))
                ->getSingle();
        return $rs["total"];
    }
    
    /**
     * @param string $campo
     * @param string $valor
     * @return LHWebEntity
     */
    public function procurarCount($campo, $valor){
        $obj = $this->class_entidade;
        if(is_array($campo)){
            $q = $this->getQueryProcurarCampoArray($obj, $campo, $valor);
        } else {
            $q = $this->getQueryProcurarCampoString($obj, $campo, $valor);
        }
        
        $q->campos(array("COUNT(" . $this->getNomeChavePrimaria(true). ") as total"));
        
        $rs = $q->getSingle();
        return $rs["total"];
    }
    
}
