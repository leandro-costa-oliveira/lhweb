<?php
namespace lhweb\view;

use Exception;
use Iterator;

/**
 * Renderizar campos de formulário de forma padronizada, assim caso necessite
 * alguma alteração em todo o sistema, basta estender a classe e customizar.
 *
 * @author loki
 */
abstract class LHFormField {
    public static $LEFT  = 1;
    public static $RIGHT = 2;
    
    protected $id    = "lhFormField";
    protected $name  = null;
    protected $title = "";
    protected $class = "";
    protected $style = "";
    protected $role  = "";
    protected $disabled = false;
    protected $data = array();
    
    /**
     * Deve renderizar o componente.
     */
    abstract public function render();
    
    /**
     * 
     * @return LHFormField
     */
    public static function id($id){
        if(!$id) {
            throw new Exception(static::class . " ERROR: É necessário setar a id do campo.");
        }
        $c = static::class;
        $o = new $c();
        $o->id = $id;
        return $o;
    }
    
    public function css($field, $val) {
        $this->style .= "$field: $val;";
        return $this;
    }
    
    protected function set($var, $val) {
        switch($var){
            case "class": // faz o append no class do css.
                $this->$var .= ($this->$var?" ":"") . htmlspecialchars($val); break;
            default:
                $this->$var = htmlspecialchars($val);
        }
    }
    
    protected function setArray($var, $val) {
        if(is_array($val) || $val instanceof Iterator){
            
            $a = array();
            foreach($val as $k => $v) {
                $a[$k] = $v;
            }
            // Hack for PHP 5.5, $this->$var[$k] = $v wasn't working
            $this->$var = array_merge($this->$var,$a);
        } else {
            array_push($this->$var, $val);
        }
    }
    
    protected function pushData($key, $val) {
        $this->data[$key] = htmlspecialchars($val);
    }
    
    public function __call($method, $args) {
        foreach($args as $val){
            if(strpos($method, "data")!==false){
                $this->pushData(strtolower(str_replace("data", "", $method)), $val);
            } else if(!property_exists($this, $method)){
                throw new Exception(static::class . " ERROR: Campo Inexistente [" . htmlspecialchars($method). "]");
            } else if(is_array($this->$method)) {
                $this->setArray($method, $val);
            } else {
                $this->set($method, $val);
            }
        }
                
        return $this;
    }
    
    public function renderGlyphIcon($icon) {
        return " <span class='glyphicon glyphicon-$icon'></span> ";
    }
    
    public function renderData(){
        $txt = "";
        
        foreach($this->data as $key => $val) {
            $val = str_replace("\"", "'", $val);
            $txt .= " data-$key=\"$val\" ";
        }
        
        return $txt;
    }
    
    public function renderHtmlAttr($attr, $val=null) {
        if(!$val){
            if(!property_exists($this, $attr)){
                throw new Exception(static::class . " RENDER HTML ATTR: Campo Inexistente [" . htmlspecialchars($attr). "]");
            }
            
            $val = $this->$attr;
        }
        
        if(!empty($val)){
            $attr = str_replace("_", "-", $attr);
            return " $attr=\"" . htmlspecialchars($val) . "\" ";
        }
    }
    
    public function renderHtmlProp($attr) {
        if(!property_exists($this, $attr)){
            throw new Exception(static::class . " RENDER HTML ATTR: Campo Inexistente [" . htmlspecialchars($attr). "]");
        }
        
        if(!empty($this->$attr) && $this->$attr){
            return " $attr ";
        }
    }
}
