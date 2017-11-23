<?php
namespace lhweb\view;

use lhweb\database\LHWebEntity;

/**
 * Representa o objeto html <button>
 *
 * @author loki
 */
class LHFSelect  extends LHFormField {
    protected $width       = 0;
    protected $class = "form-control";
    protected $text  = "";
    protected $title = "";
    protected $required = false;
    protected $showEmptyOption = true;
    protected $options = array();
    protected $value = null;
    
    public function render() {
        if($this->name === null){
            $this->name = $this->id;
        }
        
        $txt = "";
        
        if($this->width > 0) {
            $txt .= "<div class='col-sm-$this->width'>";
        }
        
        $txt .= "<select ";
        $txt .= $this->renderHtmlAttr("id");
        $txt .= $this->renderHtmlAttr("name");
        $txt .= $this->renderHtmlAttr("class");
        $txt .= $this->renderHtmlAttr("style");
        $txt .= $this->renderHtmlProp("required");
        $txt .= $this->renderHtmlProp("disabled");
        $txt .= $this->renderData();
        $txt .=  ">";
    
        if($this->showEmptyOption){
            $txt .= "<option value=''></option>";
        }
        
        foreach($this->options as $key => $val) {
            $entity = "";
            if($val instanceof LHWebEntity){
                $class_entidade = get_class($val);
                $pkName = $class_entidade::$nomeChavePrimaria;
                $key    = $val->$pkName;
                $entity = json_encode($val);    
            }
            
            $slc = $this->value == $key ? "selected":"";
            $txt .= "<option value='" . htmlspecialchars($key) . "' $slc data-entity='$entity'>" . htmlspecialchars($val) . "</option>";
        }
        
        $txt .= "</select>";
        
        
        if($this->width > 0){
            $txt .= "</div>";
        }
        
        return $txt;
    } // render

}
