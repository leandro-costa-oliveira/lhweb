<?php
namespace lhweb\view;

/**
 * Representa o objeto html <button>
 *
 * @author loki
 */
class LHFButton  extends LHFormField {
    protected $class = "";
    protected $size  = "";
    protected $text  = "LHFormButton";
    protected $title = "";
    protected $icon  = null;
    protected $iconSide = 1;
    protected $type  = "button";
    
    public function render() {
        echo "<button id='$this->id' type='$this->type' ";
        echo " class='btn" . ($this->class?" btn-$this->class":"") . ($this->size?" btn-$this->size":"")  ."'";
        echo $this->title?" title='$this->title'":"";
        echo $this->disabled?" disabled":"";
        $this->renderData();
        echo ">";
        
        if($this->icon !== null && $this->iconSide === static::$LEFT){
            $this->renderGlyphIcon($this->icon);
        }
        
        echo htmlspecialchars($this->text);
        
        if($this->icon !== null && $this->iconSide === static::$RIGHT){
            $this->renderGlyphIcon($this->icon);
        }
        echo "</button>";
    } // render

}
