<?php
namespace lhweb\database;

use JsonSerializable;
/**
 * Description of AbstractEntity
 *
 * @author loki
 */
abstract class LHWebEntity implements JsonSerializable {
    public static $nomeChavePrimaria = "id";
    public static $tipoChavePrimaria = LHDB::PARAM_INT;
    public static $tabela = null;
    
    /**
     *
     * @var AbstractEntity 
     */
    public static $joins = [];
    
    /**
     *
     * @var AbstractEntity 
     */
    public static $leftOuterJoins = [];
    
    
    public function __construct() {
    }
    
    /**
     *
     * @var array
     * Variável utilizada para configurar o mapeamento entre os campos da classe
     * e o nome das colunas no banco de dados. por ex:
     * protected $camposMap = array(
     *     "campoDaClasse" => "ColunaDoBanco"
     * );
     * Ao fazer um select, será utilizado na SQL o ColunaDoBanco, mas quando o 
     * resultado retornar será armazenado na propriedade campoDaClasse do objeto.
     * 
     */
    public static $mapaCampos = array();
    
    
    /**
     *
     * @var array
     * Tipos dos campos, deve ser declarado no formato:
     * nomeDoCampo => LHDB::PARAM_STR
     */
    public static $mapaTipos = array();
    
    /**
     *
     * @var array
     * Array contendo os campos que não devem ser gravados, 
     * sem Insert e sem Update.
     */
    public static $camposSomenteLeitura = array();
    
    /**
     *
     * @var array
     * Array de campos que podem ser inseridos mas não alterados
     */
    public static $camposNaoAlterar = array();
    
    /**
     *
     * @var array
     * Array de campos para serem evitados durante o insert, normalmente
     * campos com valores padrão do banco de dados.
     */
    public static $camposNaoInserir = array();
    
    /**
     *
     * @var array
     * 
     */
    public static $camposNaoSerializar = array();
    
    
    public function __toString() {
        $pk = static::$nomeChavePrimaria;
        return static::class . "[" . $this->$pk . "]";
    }
    
    public function jsonSerialize (){
        $ret = array();
        foreach($this as $key => $val){ 
            if(strpos($key, "__lh")===0 || in_array($key, static::$camposNaoSerializar)){
                continue;
            }
            
            if($val instanceof EntityArray){ // Armazena em formato convertível para json, no caso um array.
                $ret[$key] = $val->jsonSerialize();
            } else {
                $ret[$key] = $val;
            }
        }
        
        if(method_exists($this, "__toString")){
            $ret["toString"] = $this->__toString();
        } else {
            $ret["toString"] = static::class;
        }
        return $ret;
    }
}
