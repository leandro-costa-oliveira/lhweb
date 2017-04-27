<?php
namespace lhweb\view;
use lhweb\database\AbstractEntity;

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
        
        if($this->width > 0) {
            echo "<div class='col-sm-$this->width'>";
        }
        echo "<select ";
        $this->renderHtmlAttr("id");
        $this->renderHtmlAttr("name");
        $this->renderHtmlAttr("class");
        $this->renderHtmlProp("required");
        $this->renderHtmlProp("disabled");
        $this->renderData();
        echo ">";
    
        if($this->showEmptyOption){
            echo "<option value=''></option>";
        }
        
        foreach($this->options as $key => $val) {
            if($val instanceof AbstractEntity){
                $pkName = $val->getPkAttribute();
                $key = $val->$pkName;
            }
            
            $slc = $this->value == $key ? "selected":"";
            echo "<option value='" . htmlspecialchars($key) . "' $slc>" . htmlspecialchars($val) . "</option>";
        }
        
        echo "</select>";
        
        
        if($this->width > 0){
            echo "</div>";
        }
    } // render

}
