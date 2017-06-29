<?php

namespace lhweb\view;

/**
 * Description of LHFCheckbox
 *
 * @author loki
 */
class LHFCheckbox extends LHFormField {
    protected $text  = null;
    protected $width = null;
    protected $value = 1;
    protected $checked = false;
    protected $class = "checkbox-inline";
    
    public function render() {
        if($this->text === null) {
            $this->text = ucwords($this->id) . ":";
        }
        
        $txt = "";
        if($this->width > 0) {
            $txt .=  "<div class='col-sm-$this->width'>";
        }
        
        $txt .= "<label id='label_$this->id' class='$this->class'";
        $txt .= $this->renderHtmlAttr("style");
        $txt .= $this->renderData();
        $txt .= ">";
        $txt .= "<input type='checkbox' ";
        $txt .= $this->renderHtmlAttr("id");
        $txt .= $this->renderHtmlAttr("name");
        $txt .= $this->renderHtmlAttr("value");
        $txt .= $this->renderHtmlProp("checked");
        $txt .= "/>";
        $txt .= " $this->text</label>";
        
        
        if($this->width > 0){
            $txt .=  "</div>";
        }
        
        return $txt;
    }
}
