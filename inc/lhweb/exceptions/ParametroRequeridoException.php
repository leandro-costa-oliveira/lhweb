<?php
namespace lhweb\exceptions;

/**
 * Description of ParametroRequeridoException
 *
 * @author loki
 */
class ParametroRequeridoException extends LHWebException {
    private $param;
    
    public function __construct($param) {
        $this->param = $param;
    }
}
