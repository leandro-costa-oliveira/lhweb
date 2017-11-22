<?php
namespace lhweb\actions;

use DateTime;
use lhweb\controller\LHWebController;
use lhweb\database\LHDB;
use lhweb\database\LHWebEntity;
use lhweb\exceptions\ParametroException;
use lhweb\exceptions\ParametroRequeridoException;
use lhweb\misc\LHWebMisc;

class LHWebAction {
    /**
     *
     * @var LHWebController
     */
    protected $controller = null;
    
    /**
     *
     * @var array
     * Dados da request - $_POST e $_GET
     */
    protected $in;
    
    /**
     * 
     * @param LHWebController $controller
     */
    public function __construct($controller) {
        $this->in = array_merge(
                $this->parseRequestData($_GET),
                $this->parseRequestData($_POST)
        );
        
        $this->controller = $controller;
    }
    
    /**
     * 
     * @param array $req
     * @return array
     * Faz a leitura dos paramêtros de formulario e monta um array,
     * convertendo os arrays da request, para arrays do php.
     * Já faz o escape de HTML para evitar injeção de código.
     */
    public function parseRequestData($req){
        $ret = array();
        foreach($req as $key => $val) {
            if(is_array($val)) {
                $ret[$key] = $this->parseRequestData($val);
            } else {
                $ret[$key] = htmlspecialchars($val, ENT_QUOTES | ENT_HTML5);
            }
        }

        return $ret;
    }
    
    
    /**
     * 
     * @param type String
     * @return boolean 
     * Retorna true caso a Ação requira autenticação.
     */
    public function requireAuth($acao){
        switch($acao){
            default: return true;
        }
    }
    
    /**
     * 
     * @param type string
     * @return boolean
     * Must return true when the action is going the change Session values, like doing login.
     */
    public function requireWriteSession($acao){
        switch($acao){
            default: return false;
        }
    }
    
    public function setLHDB($db){
        $this->db = $db;
    }
    
    public function getFile($paramName){
        if(array_key_exists($paramName, $_FILES)){
            return $_FILES[$paramName];
        } else {
            return NULL;
        }
    }
    
    public function getFileName($paramName){
        if(array_key_exists($paramName, $_FILES)){
            return filter_var($_FILES[$paramName]["name"],FILTER_SANITIZE_STRING);
        } else {
            return NULL;
        }
    }
    
    public function getFileContent($paramName){
        if(array_key_exists($paramName, $_FILES)){
            return file_get_contents($_FILES[$paramName]["tmp_name"]);
        } else {
            return NULL;
        }
    }
    
    public function formatarParametroFloat($val){
        return $val;
    }
    
    /**
     * 
     * @param string $paramName
     * @param int $tipo
     * @param boolean $requerido
     * @param boolean $permitirVazio
     * @return type
     * @throws ParametroRequeridoException
     */
    private function getParametro($in, $paramName, $tipo, $requerido=false, $permitirVazio=true){
        $param = array_key_exists($paramName, $in)?$in[$paramName]:null;
        if($requerido) {
            if(!array_key_exists($paramName, $in)){
                throw new ParametroRequeridoException("O Campo $paramName é requerido");
            }
            
            if(!$permitirVazio && empty($in[$paramName])){
                throw new ParametroRequeridoException("Preencha o campo $paramName");
            }
        }
        
        switch($tipo){
            case LHDB::PARAM_INT: 
                $param = filter_var($param, FILTER_SANITIZE_NUMBER_INT);
                return is_numeric($param)?$param:null;
            case LHDB::PARAM_FLOAT:
                $param = filter_var($this->formatarParametroFloat($param), FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
                return is_numeric($param)?$param:null;
            case LHDB::PARAM_STR:
            default:
                $param = str_replace("\0", "", $param); // Removendo Null Byte, vetor de ataques.
                return filter_var($param,FILTER_SANITIZE_STRING);
        }
    }
    
    public function getParametroInt($paramName){
        return $this->getParametro($this->in, $paramName, LHDB::PARAM_INT, false, true);
    }
    
    public function getParametroFloat($paramName){
        return $this->getParametro($this->in, $paramName, LHDB::PARAM_FLOAT, false, true);
    }
    
    public function getParametroString($paramName){
        return $this->getParametro($this->in, $paramName, LHDB::PARAM_STR, false, true);
    }
    
    public function getParametroArray($arrayName, $tipo){
        $ret = [];
        if(array_key_exists($arrayName,$this->in)) {
            foreach(array_keys($this->in[$arrayName]) as $k){
                array_push($ret, $this->getParametro($this->in[$arrayName], $k, $tipo));
            }
        }
        
        return $ret;
    }
    
    public function getParametroData($paramName){
        $txt = $this->getParametro($this->in, $paramName, LHDB::PARAM_STR, false, true);
        $dt = DateTime::createFromFormat(LHWebMisc::$FORMATO_DATA_EXIBICAO, $txt);
        if($dt){
            return $dt;
        } else {
            return null;
        }
    }
    
    public function getParametroDataAsString($paramName){
        $txt = $this->getParametro($this->in, $paramName, LHDB::PARAM_STR, false, true);
        $dt = DateTime::createFromFormat(LHWebMisc::$FORMATO_DATA_EXIBICAO, $txt);
        if($dt){
            return $dt->format(LHWebMisc::$FORMATO_DATA_DB);
        } else {
            return null;
        }
    }
    
    public function requererParametroInt($paramName, $permitirVazio=false){
        return $this->getParametro($this->in, $paramName, LHDB::PARAM_INT, true, $permitirVazio);
    }
    
    public function requererParametroFloat($paramName, $permitirVazio=false){
        return $this->getParametro($this->in, $paramName, LHDB::PARAM_FLOAT, true, $permitirVazio);
    }
    
    public function requererParametroString($paramName, $permitirVazio=false){
        return $this->getParametro($this->in, $paramName, LHDB::PARAM_STR, true, $permitirVazio);
    }
    
    public function requererParametroData($paramName){
        $txt = $this->getParametro($this->in, $paramName, LHDB::PARAM_STR, true);
        $dt = DateTime::createFromFormat(LHWebMisc::$FORMATO_DATA_EXIBICAO, $txt);
        if($dt){
            return $dt;
        } else {
            throw new ParametroException("[$paramName] Data Inválida");
        }
    }
    
    public function requererParametroDataAsString($paramName){
        $txt = $this->getParametro($this->in, $paramName, LHDB::PARAM_STR, true);
        $dt = DateTime::createFromFormat(LHWebMisc::$FORMATO_DATA_EXIBICAO, $txt);
        if($dt){
            return $dt->format(LHWebMisc::$FORMATO_DATA_DB);
        } else {
            throw new ParametroException("[$paramName] Data Inválida");
        }
    }
    
    public function getChavePrimaria(){
        return $this->getParametro($this->in, $this->controller->getNomeChavePrimaria(), 
                $this->controller->getTipoChavePrimaria());
    }
    
    public function requererChavePrimaria(){
        return $this->getParametro($this->in, $this->controller->getNomeChavePrimaria(), 
                $this->controller->getTipoChavePrimaria(), true);
    }
    
    /**
     * 
     * @return LHWebEntity
     */
    public function Primeiro(){
        return $this->controller->primeiro();
    }
    
    /**
     * 
     * @return LHWebEntity
     */
    public function Ultimo(){
        return $this->controller->ultimo();
    }
    
    /**
     * 
     * @return LHWebEntity
     */
    public function Anterior(){
        $ret = $this->controller->anterior($this->getChavePrimaria());
        if(!$ret){
            $ret = $this->controller->ultimo();
        }
        
        return $ret;
    }
    
    /**
     * 
     * @return LHWebEntity
     */
    public function Proximo(){
        $ret = $this->controller->proximo($this->getChavePrimaria());
        if(!$ret){
            $ret = $this->controller->primeiro();
        }
        return $ret;
    }
    
    /**
     * 
     * @return LHWebEntity
     */
    public function Mover(){
        return $this->controller->getByPK($this->getChavePrimaria());
    }
    
    /**
     * 
     * @return type
     * @throws RegistroNaoEncontrado
     */
    public function Apagar(){
        $pk  = $this->getChavePrimaria();
        return $this->controller->apagar($pk);
    }
    
    public function Salvar(){
        return $this->controller->salvar($this->buildEntityFromRequest());
    }
    
    public function buildEntityFromRequest(){
        $classe_entidade = $this->controller->class_entidade;
        $entidade = new $classe_entidade();
        
        foreach($entidade as $key => $val){
            $entidade->$key = $this->getParametro($this->in, $key,  $this->controller->getTipoCampo($key));
        }
        
        return $entidade;
    }
}
