<?php
namespace lhweb\dao;

abstract class AbstractDAO {
    /**
     *
     * @var \lhweb\misc\LHDb
     */
    protected $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    
    public abstract function getBasicMoveQuery();
    public abstract function makeObjectFromRs($rs);
    public abstract function inserir($obj);
    public abstract function alterar($obj);
    public abstract function apagar ($obj);
}
