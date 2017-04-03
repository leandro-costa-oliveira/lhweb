<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lhweb\misc;

/**
 * Description of LHWebMisc
 *
 * @author Leandro
 */
class LHWebMisc {
    
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
