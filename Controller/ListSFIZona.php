<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListSFIZona extends ListController
{
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'network';
        $data['title'] = 'zones';
        $data['icon'] = 'fas fa-map';
        return $data;
    }

    protected function createViews()
    {
        $this->addView('ListSFIZona', 'SFIZona', 'zones', 'fas fa-map');
    }
}