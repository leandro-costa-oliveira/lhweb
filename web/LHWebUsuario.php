<?php
use lhweb\database\LHWebEntity;

class LHWebUsuario extends LHWebEntity {
    public $id;
    public $nome;
    public $usuario;
    
    public function __toString() {
        return $this->id . " - " . $this->nome;
    }
    
}