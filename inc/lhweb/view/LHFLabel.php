<?php
namespace lhweb\view;

/**
 * Description of LHFLabel
 *
 * @author loki
 */
class LHFLabel extends LHFormField {
    protected $text  = "LHFormLabel";
    protected $width = 1;
    
    public function render() {
        echo "<label for='$this->id' class='col-sm-$this->width control-label $this->class'>$this->text</label>";
    }

}
