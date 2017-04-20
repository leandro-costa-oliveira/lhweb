<?php
namespace lhweb\view;

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
    protected $class = "";
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
            throw new \Exception(static::class . " ERROR: É necessário setar a id do campo.");
        }
        $c = static::class;
        $o = new $c();
        $o->id = $id;
        return $o;
    }
    
    protected function set($var, $val) {
        if(!property_exists($this, $var)){
            throw new \Exception(static::class . " ERROR: Campo Inexistente [" . htmlspecialchars($var). "]");
        }
        
        if($this->$var) {
            $this->$var .= " ";
        }
        $this->$var .= htmlspecialchars($val);
    }
    
    protected function pushData($key, $val) {
        $this->data[$key] = htmlspecialchars($val);
    }
    
    public function __call($method, $args) {
        foreach($args as $val){
            if(strpos($method, "data")!==false){
                $this->pushData(strtolower(str_replace("data", "", $method)), $val);
            } else {
                $this->set($method, $val);
            }
        }
                
        return $this;
    }
    
    public function renderGlyphIcon($icon) {
        echo " <span class='glyphicon glyphicon-$icon'></span> ";
    }
    
    public function renderData(){
        foreach($this->data as $key => $val) {
            echo " data-$key=\"$val\" ";
        }
    }
    
    public function renderHtmlAttr($attr) {
        if(!property_exists($this, $attr)){
            throw new \Exception(static::class . " RENDE HTML ATTR: Campo Inexistente [" . htmlspecialchars($attr). "]");
        }
        
        if(!empty($this->$attr)){
            echo " $attr=\"" . $this->$attr . "\" ";
        }
    }
    
    public function renderHtmlProp($attr) {
        if(!property_exists($this, $attr)){
            throw new \Exception(static::class . " RENDE HTML ATTR: Campo Inexistente [" . htmlspecialchars($attr). "]");
        }
        
        if(!empty($this->$attr) && $this->$attr){
            echo " $attr ";
        }
    }
}
