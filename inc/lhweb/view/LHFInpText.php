<?php
namespace lhweb\view;
/**
 * Description of LHFormInputText
 *
 * @author loki
 */
class LHFInpText extends LHFormField {
    protected $width       = 0;
    protected $maxlength   = null;
    protected $placeholder = "";
    protected $value       = "";
    protected $required    = false;
    protected $readonly    = false;
    protected $class       = "form-control";
    
    public function render() {
        if($this->name === null){
            $this->name = $this->id;
        }
        $txt = "";
        
        if($this->width > 0) {
            $txt .=  "<div class='col-sm-$this->width'>";
        }
        
        $txt .= "<input type='text' ";
        $txt .= $this->renderHtmlAttr("class");
        $txt .= $this->renderHtmlAttr("id");
        $txt .= $this->renderHtmlAttr("name");
        $txt .= $this->renderHtmlAttr("placeholder");
        $txt .= $this->renderHtmlAttr("value");
        $txt .= $this->renderHtmlAttr("maxlength");
        $txt .= $this->renderHtmlAttr("style");
        $txt .= $this->required?" required":"";
        $txt .= $this->readonly?" readonly":"";
        $txt .= $this->disabled?" disabled":"";
        $txt .= $this->renderData();
        $txt .= " />";
        
        if($this->width > 0){
            $txt .=  "</div>";
        }
        
        return $txt;
    }

}
