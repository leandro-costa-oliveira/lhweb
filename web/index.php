<?php
error_reporting(E_ALL);
require_once("../inc/autoloader.php");
require_once("LHWebExample.php");
header('Content-Type: text/html; charset=utf-8');

use lhweb\controller\LHWebController;
use lhweb\database\MysqlDB;
use lhweb\view\LHFButton;
use lhweb\view\LHFInpText;
use lhweb\view\LHFLabel;

$db = new MysqlDB("localhost","lhprovedordev","lhweb","lhweb");
$ctl = new LHWebController(LHWebExample::class);
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <title></title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

        
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

        <!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    </head>
    
        <h1>LHWEB FRAMEWORK</h1>
        <p>Teste Page</p>
        
        <div class="container">
            <div>
                <form method="GET" class="form-horizontal">
                    <div class="form-group">
                        <?php echo LHFLabel::id("inp_name")->text("Nome:")->width(2)->dataTeste("true")->render(); ?>
                        <?php echo LHFInpText::id("nome")->width(6)->class("text-mutted")->render(); ?>

                        <?php echo LHFLabel::id("valor")->text("Valor:")->width(2)->dataTeste("true")->render(); ?>
                        <?php echo LHFInpText::id("valor")->width(2)->class("text-mutted")->render(); ?>
                    </div>
                    
                    <div class="form-group">
                        <?php echo LHFLabel::id("descp")->text("Descrição:")->width(2)->dataTeste("true")->render(); ?>
                        <?php echo LHFInpText::id("descp")->width(6)->class("text-mutted")->render(); ?>
                        
                        <div class="col-sm-4 text-right">
                        <?php echo LHFButton::id("bt_novo")->type("submit")->icon("usd")->class("btn-primary")->text("Novo Registro")->render(); ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <br/>
        <br/>
        <br/>
        
        <pre>
        <?php
        try { 
            echo "\n";
            $tini = microtime(true);
            $ctl->getBy("nome", "","isNull");
            if(array_key_exists("nome", $_GET) && array_key_exists("valor", $_GET) && array_key_exists("descp", $_GET)){
                echo "\n###########################################################################################\n";
                $e = $ctl->getBy("nome", filter_var($_GET["nome"], FILTER_SANITIZE_STRING));
                if(!$e){
                    $e = new LHWebExample();
                }
                
                echo "\nSAVING OBJECT:";
                print_r($e);
                
                $e->nome  = filter_var($_GET["nome"], FILTER_SANITIZE_STRING);
                $e->valor = filter_var($_GET["valor"], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
                $e->descp = filter_var($_GET["descp"], FILTER_SANITIZE_STRING);
                $e2 = $ctl->salvar($e);
                
                echo "\nSAVED OBJECT:";
                print_r($e2);
            }
            
            echo "\n###########################################################################################\n";
            $primeiro = $ctl->primeiro();
            $ultimo   = $ctl->ultimo();
            echo "\nBY PK[4]:" . print_r($ctl->getByPK(4),true);
            echo "\nPRIMEIRO:" . print_r($primeiro,true);
            echo "\nULTIMO  :" . print_r($ultimo,true);
            echo "\nPROXIMO  A [" . ($primeiro?$primeiro->id:"") . "]:" . print_r($primeiro?$ctl->proximo($primeiro->id):"",true);
            echo "\nANTERIOR A [" . ($ultimo?$ultimo->id:"") . "]:"   . print_r($ultimo?$ctl->anterior($ultimo->id):"",true);
            
            echo "\n###########################################################################################";
            echo "\n#### LISTA:\n";
            foreach($ctl->listar() as $key => $val){
                echo "$key => "   . $val . "<br/>";
            }
            
            echo "\n#### LISTA JSON:\n";
            echo json_encode($ctl->listar(),JSON_PRETTY_PRINT);
            $tend = microtime(true); 
            
            echo "\n\n##### EXECUTION TIME: " . round($tend - $_SERVER["REQUEST_TIME_FLOAT"], 4) . "us \n";
        } catch(Exception $ex) {
            echo "ERROR:" . $ex->getMessage() . "\n";
            echo "TRACE:" . $ex->getTraceAsString() . "\n";
        }
        ?>
        </pre>
    
</html>
 