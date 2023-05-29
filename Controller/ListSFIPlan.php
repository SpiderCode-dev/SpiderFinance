<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListSFIPlan extends ListController
{
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'services';
        $data['title'] = 'plans';
        $data['icon'] = 'fas fa-list';
        return $data;
    }

    protected function createViews()
    {
        $this->addView('ListSFIPlan', 'SFIPlan', 'plans', 'fas fa-list');
    }
}