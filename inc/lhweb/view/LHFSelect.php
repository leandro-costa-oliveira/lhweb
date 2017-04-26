<?php
namespace lhweb\view;
use lhweb\database\AbstractEntity;

/**
 * Representa o objeto html <button>
 *
 * @author loki
 */
class LHFSelect  extends LHFormField {
    protected $class = "form-control";
    protected $text  = "";
    protected $title = "";
    protected $showEmptyOption = true;
    protected $options = array();
    
    public function render() {
        if($this->name === null){
            $this->name = $this->id;
        }
        
        echo "<select ";
        $this->renderHtmlAttr("id");
        $this->renderHtmlAttr("name");
        $this->renderHtmlAttr("class");
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
            echo "<option value='" . htmlspecialchars($key) . "'>" . htmlspecialchars($val) . "</option>";
        }
        
        echo "</select>";
    } // render

}
