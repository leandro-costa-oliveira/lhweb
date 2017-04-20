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
    protected $required    = false;
    protected $readonly    = false;
    
    public function render() {
        echo "<div class='col-sm-$this->width'>";
        echo "<input type='text' class='form-control $this->class' id='$this->id' ";
        echo $this->name?" name='$this->name'":" name='$this->id'";
        echo " placeholder='$this->placeholder' value='$this->value' ";
        echo $this->required?" required":"";
        echo $this->readonly?" readonly":"";
        echo $this->disabled?" disabled":"";
        echo " />";
        echo "</div>";
    }

}
