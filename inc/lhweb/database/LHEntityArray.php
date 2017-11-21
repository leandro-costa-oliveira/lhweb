<?php
namespace lhweb\database;

use Countable;
use Iterator;
use JsonSerializable;
use lhweb\controller\LHWebController;

/**
 * Description of EntityArray
 *
 * @author loki
 */
class LHEntityArray implements Iterator, Countable, JsonSerializable {
    /**
     *
     * @var array
     */
    private $array;
    
    /**
     *
     * @var LHWebController
     */
    private $ctl;
    private $idx = 0;
    
    /**
     * 
     * @param array $array
     * @param LHWebController $ctl
     */
    public function __construct($array, $ctl){
        $this->array = $array?$array:array();
        $this->ctl = $ctl;
    }
    
    public function count($mode = COUNT_NORMAL) {
        return count($this->array, $mode);
    }

    public function current() { 
        return $this->ctl->getEntityFromRS($this->array[$this->idx]);
    }

    public function key() {
        return $this->idx;
    }

    public function next() {
        $this->idx++;
    }

    public function rewind() {
        $this->idx = 0;
    }

    public function valid() {
        return array_key_exists($this->idx, $this->array);
    }

    public function jsonSerialize() {
        $ret = array();
        foreach($this as $item){
            array_push($ret, $item);
        }
        
        return $ret;
    }

}
