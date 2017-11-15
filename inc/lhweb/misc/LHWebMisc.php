<?php
namespace lhweb\misc;

/**
 * Description of LHWebMisc
 *
 * @author Leandro
 */
class LHWebMisc {
    public static $FORMATO_DATA_EXIBICAO = "d/m/Y";
    public static $FORMATO_DATAHORA_EXIBICAO = "d/m/Y H:i";
    public static $FORMATO_DATA_DB= "Y-m-d";
    public static $FORMATO_DATAHORA_DB= "Y-m-d H:i";
    
    public function parseNumeroDecimal($valor,$decimals=2){
        $v = "$valor";
        $decimal = substr($v, strlen($v)-3,1);
        if($decimal==","){
            return round(floatval(str_replace(",",".",str_replace(".","",$v))),$decimals);
        } else if($decimal=="."){
            return round(floatval(str_replace(",","",$v)),$decimals);
        } else {
            return intval($valor);
        }
    }
}
