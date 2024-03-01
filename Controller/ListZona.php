<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListZona extends ListController
{
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'manage-network';
        $data['title'] = 'zones';
        $data['icon'] = 'fas fa-map';
        return $data;
    }

    protected function createViews()
    {
        $this->addView('ListZona', 'Zona', 'zones', 'fas fa-map');
    }
}