<?php

namespace FacturaScripts\Plugins\SpiderFinance\Extension\Model;
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