<?php
namespace lhweb\misc;
/**
 * Description of DataHora
 *
 * @author Leandro
 */
class LHWebDataHora extends \DateTime {
    
    private $formatoExibicao = "d/m/Y H:i";
    private $formatoDb       = "Y-m-d H:i";
    
    public function __construct($time="now", $timezone=NULL) {
        parent::__construct($time, $timezone);
    }
    
    function getFormatoExibicao() {
        return $this->formatoExibicao;
    }

    function setFormatoExibicao($formatoExibicao) {
        $this->formatoExibicao = $formatoExibicao;
    }
    
    function getFormatoDb() {
        return $this->formatoDb;
    }

    function setFormatoDb($formatoDb) {
        $this->formatoDb = $formatoDb;
    }
    
    public function __toString() {
        return $this->format($this->formatoExibicao);
    }
    
    public function formatar($formato){
        return $this->format($formato);
    }

    /**
     * Return difference between $this and $now
     *
     * @param Datetime|String $now
     * @return DateInterval
     */
    public function subtrair($now = 'NOW') {
        if(!($now instanceOf DateTime)) {
            $now = new DateTime($now);
        }
        return parent::diff($now);
    }

}
