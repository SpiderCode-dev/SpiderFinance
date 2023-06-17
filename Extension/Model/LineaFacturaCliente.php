<?php

namespace FacturaScripts\Plugins\SpiderFinance\Extension\Model;

class LineaFacturaCliente
{
    public function saveBefore() {
        return function () {
            $product = (new \FacturaScripts\Dinamic\Model\Producto())->get($this->idproducto);
            if ($product && $product->idplan) {
                $this->idplan = $product->idplan;
            }
        };
    }
}