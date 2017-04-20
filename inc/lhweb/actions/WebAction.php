<?php
namespace lhweb\actions;

use \lhweb\database\LHDB;
use \lhweb\database\AbstractEntity;
use \lhweb\controller\AbstractController;
use \lhweb\exceptions\ParametroRequeridoException;
use \lhweb\exceptions\RegistroNaoEncontrado;

abstract class WebAction {
    /**
     *
     * @var LHDB
     */
    protected $db;
    
    /**
     *
     * @var AbstractController
     */
    protected $controller;
    
    /**
     *
     * @var array
     * Dados da request - $_POST e $_GET
     */
    protected $in;
    
    
    public abstract function buildObjectFromRequest();
    public abstract function initController();
    
    public function __construct() {
        $get  = $this->parseRequestData($_GET);
        $post = $this->parseRequestData($_POST);
        $this->in = array_merge($get,$post);
    }
    
    public function initDatabase() {
        $this->db = LHDB::getConnection();
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
            default:
                return true;
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
    
    public function setDb($db){
        $this->db = $db;
    }
    
    public function getFile($paramName){
        error_log("GET FILE[$paramName]:" . print_r($_FILES));
        throw new Exception("LWHEB::WebAction::STUB [GET FILE] $paramName");
    }
    
    public function getFileName($paramName){
        error_log("GET FILE NAME [$paramName]:" . print_r($_FILES));
        throw new Exception("LWHEB::WebAction::STUB [GET FILE NAME] $paramName");
    }
    
    public function getFileContent($paramName){
        error_log("GET FILE CONTENT [$paramName]:" . print_r($_FILES));
        throw new Exception("LWHEB::WebAction::STUB [GET FILE CONTENT] $paramName");
    }
    
    /**
     * 
     * @param string $paramName
     * @param int $tipo
     * @param boolean $requerido
     * @param boolean $permitirVazio
     * @return type
     * @throws \lhweb\exceptions\ParametroRequeridoException
     */
    public function getParametro($paramName, $tipo, $requerido=false, $permitirVazio=true){
        $param = array_key_exists($paramName, $this->in)?$this->in[$paramName]:null;
        if($requerido) {
            if(!array_key_exists($paramName, $this->in)){
                throw new ParametroRequeridoException($paramName);
            }
            
            if(!$permitirVazio && empty($this->in[$paramName])){
                throw new ParametroRequeridoException($paramName);
            }
        } 
        
        switch($tipo){
            case "int"   : return filter_var($param, FILTER_SANITIZE_NUMBER_INT);
            case "float" : return filter_var($param, FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
            case "string":
            default:
                $param = str_replace("\0", "", $param); // Removendo Null Byte, vetor de ataques.
                return filter_var($param,FILTER_SANITIZE_STRING);
        }
    }
    
    public function getParametroInt($paramName, $requerido=false, $permitirVazio=true){
        return $this->getParametro($paramName, "int", $requerido, $permitirVazio);
    }
    
    public function getParametroFloat($paramName, $requerido=false, $permitirVazio=true){
        return $this->getParametro($paramName, "float", $requerido, $permitirVazio);
    }
    
    public function getParametroString($paramName, $requerido=false, $permitirVazio=true){
        return $this->getParametro($paramName, "string", $requerido, $permitirVazio);
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function Primeiro(){
        return $this->controller->primeiro();
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function Ultimo(){
        return $this->controller->ultimo();
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function Anterior(){
        $pk  = $this->getParamenterRequired($this->controller->getPkName(), FILTER_SANITIZE_NUMBER_INT);
        $ret = $this->controller->anterior($pk);
        if(!$ret){
            $ret = $this->controller->ultimo();
        }
        
        return $ret;
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function Proximo(){
        $pk  = $this->getParamenterRequired($this->controller->getPkName(), FILTER_SANITIZE_NUMBER_INT);
        $ret = $this->controller->proximo($pk);
        if(!$ret){
            $ret = $this->controller->primeiro();
        }
        return $ret;
    }
    
    /**
     * 
     * @return AbstractEntity
     */
    public function Mover(){
        $pk  = $this->getParamenterRequired($this->controller->getPkName(), FILTER_SANITIZE_NUMBER_INT);
        return $this->controller->getByPK($pk);
    }
    
    /**
     * 
     * @return type
     * @throws RegistroNaoEncontrado
     */
    public function Apagar(){
        $pk  = $this->getParamenterRequired($this->controller->getPkName(), FILTER_SANITIZE_NUMBER_INT);
        $obj = $this->controller->getByPK($pk);
        
        if($obj){
            return $this->controller->apagar($obj);
        } else {
            throw new RegistroNaoEncontrado();
        }
    }
    
    public function Salvar(){
        $obj = $this->buildObjectFromRequest();
        return $this->controller->salvar($obj);
    }
}
