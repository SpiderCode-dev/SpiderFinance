<?php

use FacturaScripts\Plugins\SpiderFinance\Model\Plan;

class Producto
{
    public function getPlan()
    {
        return function() {
            if ($this->idplan) {
                return (new Plan())->get($this->idplan);
            }

            return null;
        };
    }
}