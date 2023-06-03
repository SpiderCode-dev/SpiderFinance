<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListCajaNap extends ListController
{
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'manage-network';
        $data['title'] = 'nap-boxes';
        $data['icon'] = 'fas fa-boxes';
        return $data;
    }

    protected function createViews()
    {
        $this->addView('ListCajaNap', 'CajaNap', 'nap-boxes', 'fas fa-boxes');
    }
}