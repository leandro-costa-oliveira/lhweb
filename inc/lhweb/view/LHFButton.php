<?php
namespace lhweb\view;

/**
 * Representa o objeto html <button>
 *
 * @author loki
 */
class LHFButton  extends LHFormField {
    protected $class = "btn";
    protected $text  = "";
    protected $title = "";
    protected $icon  = null;
    protected $iconSide = 1;
    protected $type  = "button";
    
    public function render() {
        if($this->name === null){
            $this->name = $this->id;
        }
        
        $txt = "<button ";
        $txt .= $this->renderHtmlAttr("id");
        $txt .= $this->renderHtmlAttr("name");
        $txt .= $this->renderHtmlAttr("class");
        $txt .= $this->renderHtmlAttr("type");
        $txt .= $this->renderHtmlAttr("title");
        $txt .= $this->renderHtmlProp("disabled");
        $txt .= $this->renderHtmlAttr("style");
        $txt .= $this->renderData();
        $txt .= ">";
        
        if($this->icon !== null && $this->iconSide === static::$LEFT){
            $txt .= $this->renderGlyphIcon($this->icon);
        }
        
        $txt .= htmlspecialchars($this->text);
        
        if($this->icon !== null && $this->iconSide === static::$RIGHT){
            $txt .= $this->renderGlyphIcon($this->icon);
        }
        $txt .= "</button>";
        
        return $txt;
    } // render

}
