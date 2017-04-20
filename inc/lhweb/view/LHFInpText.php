<?php
namespace lhweb\view;
/**
 * Description of LHFormInputText
 *
 * @author loki
 */
class LHFInpText extends LHFormField {
    protected $width       = 4;
    protected $placeholder = "";
    protected $value       = "";
    
    public function render() {
        echo "<div class='col-sm-$this->width'>";
        echo "<input type='text' class='form-control $this->class' id='$this->id' placeholder='$this->placeholder' value='$this->value' />";
        echo "</div>";
    }

}
