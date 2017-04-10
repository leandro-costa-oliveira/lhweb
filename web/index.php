<?php
require_once("../inc/autoloader.php");
require_once("LhWebEntity.php");
header('Content-Type: text/html; charset=utf-8');

use \lhweb\database\MysqlDB;
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <title></title>
    </head>
    <body>
        <h1>LHWEB FRAMEWORK</h1>
        <p>Teste Page</p>
        
        <pre>
        <?php
        try { 
            $tini = microtime(true);
            $db = new MysqlDB("localhost","lhweb","lhweb","lhweb");
            
            $primeiro = LHWebEntity::primeiro();
            $ultimo   = LHWebEntity::ultimo();
            echo "\nBY PK[4]:" . print_r(LHWebEntity::getByPK(4),true);
            echo "\nPRIMEIRO:" . print_r($primeiro,true);
            echo "\nULTIMO  :" . print_r($ultimo,true);
            echo "\nPROXIMO  A [" . ($primeiro?$primeiro->id:"") . "]:" . print_r($primeiro?LHWebEntity::proximo($primeiro->id):"",true);
            echo "\nANTERIOR A [" . ($ultimo?$ultimo->id:"") . "]:"   . print_r($ultimo?LHWebEntity::anterior($ultimo->id):"",true);
            
            
            if(array_key_exists("nome", $_GET) && array_key_exists("valor", $_GET)){
                echo "\n###########################################################################################\n";
                $e = LHWebEntity::getBy("nome", filter_var($_GET["nome"], FILTER_SANITIZE_STRING));
                if(!$e){
                    $e = new LHWebEntity();
                }
                
                $e->editMode();
                $e->nome  = filter_var($_GET["nome"], FILTER_SANITIZE_STRING);
                $e->valor = filter_var($_GET["valor"], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
                
                 
                echo "\nSAVING OBJECT:";
                print_r($e);
                
                $e->salvar();
                
                echo "\nSAVED OBJECT:";
                print_r($e);
            }
             
            
            echo "\n###########################################################################################";
            echo "\n#### LISTA:\n";
            foreach(LHWebEntity::listar() as $key => $empresa){
                echo "$key => "   . print_r($empresa,true);
            }
            
            $tend = microtime(true); 
            
            echo "\n\n##### EXECUTION TIME: [$tini -> $tend]" . ($tend - $tini) . "\n";
        } catch(Exception $ex) {
            echo "ERROR:" . $ex->getMessage() . "\n";
            echo "TRACE:" . $ex->getTraceAsString() . "\n";
        }
        ?>
        </pre>
    </body>
</html>
