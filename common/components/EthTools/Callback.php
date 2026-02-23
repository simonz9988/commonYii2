<?php
namespace common\components\EthTools;

class Callback{
    public function __invoke($error,$result){
        if($error) throw $error;
        $this->result = $result;
    }
}

