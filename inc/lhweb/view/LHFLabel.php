<?php
namespace lhweb\view;

/**
 * Description of LHFLabel
 *
 * @author loki
 */
class LHFLabel extends LHFormField {
    protected $text  = null;
    protected $width = 1;
    
    public function render() {
        if($this->text === null) {
            $this->text = ucwords($this->id) . ":";
        }
        $txt = "<label id='label_$this->id' for='$this->id' class='col-sm-$this->width control-label $this->class'";
        $txt .= $this->renderHtmlAttr("style");
        $txt .= $this->renderData();
        $txt .= ">";
        $txt .= "$this->text</label>";
        
        return $txt;
    }

}
