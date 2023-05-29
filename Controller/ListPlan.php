<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListPlan extends ListController
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
        $this->addView('ListPlan', 'Plan', 'plans', 'fas fa-list');
    }
}