<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListClienteInstalacion extends ListController
{
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'customers';
        $data['title'] = 'installations';
        $data['icon'] = 'fas fa-wrench';
        return $data;
    }

    protected function createViews()
    {
        $this->addView('ListClienteInstalacion', 'ClienteInstalacion', 'installations', 'fas fa-wrench');
    }
}