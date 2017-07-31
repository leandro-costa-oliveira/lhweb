<?php
use lhweb\database\AbstractEntity;

/**
 * Entidade de testes criada para demonstrar como Extender uma AbstractEntity.
 */
class LHWebEntity extends AbstractEntity {
    public $id;
    public $nome;
    public $valor;
    public $descp;
    
    protected static $camposMap = array(
        "id",
        "nome",
        "valor",
        "descp" => "DESCRICAO"
    );
    
    
    public function __toString() {
        return $this->id . ":" . $this->nome . " - " . $this->valor;
    }

}