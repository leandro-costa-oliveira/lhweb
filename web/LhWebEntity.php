<?php
use lhweb\database\LHDB;
use lhweb\database\AbstractEntity;

/**
 * Entidade de testes criada para demonstrar como Extender uma AbstractEntity.
 */
class LHWebEntity extends AbstractEntity {
    /**
     *
     * @var int
     */
    public $id;
    
    /**
     *
     * @var string
     */
    public $nome;
    
    /**
     *
     * @var float
     */
    public $valor;
    public static $valorTipo = LHDB::PARAM_INT;
}