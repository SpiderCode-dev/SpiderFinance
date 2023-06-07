<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;


class ListFacturaCliente extends \FacturaScripts\Core\Controller\ListFacturaCliente
{
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'sales';
        $data['title'] = 'receipts';
        return $data;
    }

    protected function createViews()
    {
        // listado de facturas de cliente
        $this->createViewSales('ListFacturaCliente', 'FacturaCliente', 'payments');

        // si el usuario solamente tiene permiso para ver lo suyo, no añadimos el resto de pestañas
        if ($this->permissions->onlyOwnerData) {
            return;
        }

        // líneas de facturas de cliente
        $this->createViewLines('ListLineaFacturaCliente', 'LineaFacturaCliente');

        // recibos de cliente
        $this->createViewReceipts();

        // facturas rectificativas
        $this->createViewRefunds();
    }
}