<?php
namespace lhweb\database;

/**
 * Description of EntityArray
 *
 * @author loki
 */
class EntityArray implements \Iterator, \Countable, \JsonSerializable {
    /**
     *
     * @var array
     */
    private $array;
    
    private $entityClass;
    private $idx = 0;
    
    public function __construct($array, $entityClass){
        $this->array = $array?$array:array();
        $this->entityClass = $entityClass;
    }
    
    public function count($mode = 'COUNT_NORMAL') {
        return count($this->array, $mode);
    }

    public function current() {
        $c = $this->entityClass;
        return $c::makeFromRs($this->array[$this->idx]);
    }

    public function key() {
        return $this->idx;
    }

    public function next() {
        $c = $this->entityClass;
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
