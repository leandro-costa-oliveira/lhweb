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
    public $usuario_id;
    
    /**
     *
     * @var LHWebUsuario
     */
    public $usuario;
    
    public static $mapaCampos = array(
        "descp" => "DESCRICAO"
    );
    
    public static $leftOuterJoins = [
        "usuario" => [LHWebUsuario::class, "usuario_id"]
    ];
    
    public function __toString() {
        return $this->id . ":" . $this->nome . " - " . $this->valor;
    }

}