<?php
namespace lhweb\actions;

use \lhweb\database\LHDB;
use \lhweb\database\AbstractEntity;
use \lhweb\controller\AbstractController;
use \lhweb\exceptions\ParametroRequeridoException;

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
    
    
    public static $PARAM_INT = "int";
    public static $PARAM_FLOAT = "float";
    public static $PARAM_STRING = "string";
    
    public abstract function buildObjectFromRequest();
    public abstract function initController();
    
    public function __construct() {
        $get  = $this->parseRequestData($_GET);
        $post = $this->parseRequestData($_POST);
        $this->in = array_merge($get,$post);
    }
    
    public function initDatabase(LHDB $db=null) {
        if($db===null){
            $this->db = LHDB::getConnection();
        } else {
            $this->db = $db;
        }
    }
    
    public function getController(){
        return $this->controller;
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
                throw new ParametroRequeridoException("O Campo $paramName é requerido");
            }
            
            if(!$permitirVazio && empty($this->in[$paramName])){
                throw new ParametroRequeridoException("Preencha o campo $paramName");
            }
        }
        
        switch($tipo){
            case static::$PARAM_INT   : return filter_var($param, FILTER_SANITIZE_NUMBER_INT);
            case static::$PARAM_FLOAT : return filter_var($param, FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
            case static::$PARAM_STRING:
            default:
                $param = str_replace("\0", "", $param); // Removendo Null Byte, vetor de ataques.
                return filter_var($param,FILTER_SANITIZE_STRING);
        }
    }
    
    public function getParametroInt($paramName, $requerido=false, $permitirVazio=true){
        return $this->getParametro($paramName, static::$PARAM_INT, $requerido, $permitirVazio);
    }
    
    public function getParametroFloat($paramName, $requerido=false, $permitirVazio=true){
        return $this->getParametro($paramName, static::$PARAM_FLOAT, $requerido, $permitirVazio);
    }
    
    public function getParametroString($paramName, $requerido=false, $permitirVazio=true){
        return $this->getParametro($paramName, static::$PARAM_STRING, $requerido, $permitirVazio);
    }
    
    public function getPk(){
        return $this->getParametro($this->controller->getPkName(), FILTER_SANITIZE_NUMBER_INT,true,false);
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
        $pk  = $this->getPk();
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
        $pk  = $this->getPk();
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
        $pk  = $this->getPk();
        return $this->controller->getByPK($pk);
    }
    
    /**
     * 
     * @return type
     * @throws RegistroNaoEncontrado
     */
    public function Apagar(){
        $pk  = $this->getPk();
        return $this->controller->apagar($pk);
    }
    
    public function Salvar(){
        $obj = $this->buildObjectFromRequest();
        return $this->controller->salvar($obj);
    }
    
    public function get($obj, $var) {
        if($obj===null) {
            return;
        }
        
        if(!property_exists($obj, $var)){
            return;
        }
        
        return htmlspecialchars($obj->$var);
    }
}
