<?php
use lhweb\database\LHWebEntity;

/**
 * Entidade de testes criada para demonstrar como Extender uma AbstractEntity.
 */
class LHWebExample extends LHWebEntity {
    public $id;
    public $nome;
    public $valor;
    public $descp;
    
    public static $mapaCampos = array(
        "id",
        "nome",
        "valor",
        "descp" => "DESCRICAO"
    );
    
    
    public function __toString() {
        return $this->id . ":" . $this->nome . " - " . $this->valor;
    }

}