<?php

namespace FacturaScripts\Plugins\SpiderFinance\Extension\Controller;

class ListFacturaCliente
{
    public function createViews() {
        return function () {
            $viewName = 'ListAbono';
            $this->addView($viewName, 'Abono', 'Abonos', 'fas fa-file-invoice-dollar');
            $this->addOrderBy($viewName, ['created_at'], 'date', 2);
            $this->addSearchFields($viewName, ['observations', 'concept']);
            $this->addFilterPeriod($viewName, 'date', 'period', 'created_at');
            $this->addFilterAutocomplete($viewName, 'codcliente', 'customer', 'codcliente', 'Cliente', 'codcliente', 'nombre');
        };
    }
}