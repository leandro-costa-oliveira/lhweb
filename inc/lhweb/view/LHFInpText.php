<?php
namespace lhweb\view;
/**
 * Description of LHFormInputText
 *
 * @author loki
 */
class LHFInpText extends LHFormField {
    protected $width       = 4;
    protected $max_length   = null;
    protected $placeholder = "";
    protected $value       = "";
    protected $required    = false;
    protected $readonly    = false;
    protected $class       = "form-control";
    
    public function render() {
        if($this->name === null){
            $this->name = $this->id;
        }
        
        echo "<div class='col-sm-$this->width'>";
        echo "<input type='text' ";
        $this->renderHtmlAttr("class");
        $this->renderHtmlAttr("id");
        $this->renderHtmlAttr("name");
        $this->renderHtmlAttr("placeholder");
        $this->renderHtmlAttr("value");
        $this->renderHtmlAttr("max_length");
        echo $this->required?" required":"";
        echo $this->readonly?" readonly":"";
        echo $this->disabled?" disabled":"";
        $this->renderData();
        echo " />";
        echo "</div>";
    }

}
