<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListPlan extends ListController
{
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'manage-network';
        $data['title'] = 'plans';
        $data['icon'] = 'fas fa-wifi';
        return $data;
    }

    protected function createViews()
    {
        $this->addView('ListPlan', 'Plan', 'plans', 'fas fa-list');
    }
}