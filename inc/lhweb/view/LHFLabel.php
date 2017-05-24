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
        echo "<label id='label_$this->id' for='$this->id' class='col-sm-$this->width control-label $this->class'";
        $this->renderHtmlAttr("style");
        $this->renderData();
        echo ">";
        echo "$this->text</label>";
    }

}
