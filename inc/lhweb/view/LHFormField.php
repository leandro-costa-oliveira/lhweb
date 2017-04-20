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
    protected $class = "";
    
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
    
    public function __call($method, $args) {
        if(count($args) != 1) {
            throw new \Exception(static::class . " ERROR: PASSE UM ÚNICO VALOR]");
        }
        
        if(property_exists($this, $method)){
            $this->$method = htmlspecialchars($args[0]);
        } else {
            throw new \Exception(static::class . " ERROR: Campo Inexistente[" . htmlspecialchars($method). "]");
        }
                
        return $this;
    }
    
    public function renderGlyphIcon($icon) {
        echo " <span class='glyphicon glyphicon-$icon'></span> ";
    }
}
