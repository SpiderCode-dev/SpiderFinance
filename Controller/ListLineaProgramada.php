<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListLineaProgramada extends ListController
{
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'sales';
        $data['title'] = 'services';
        $data['icon'] = 'fas fa-calendar-plus';
        return $data;
    }

    protected function createViews()
    {
        $viewName = 'ListLineaProgramada';
        $this->addView($viewName, 'LineaProgramada', '', 'fas fa-calendar-plus');
        $this->setSettings($viewName, 'btnNew', false);
        $this->setSettings($viewName, 'btnDelete', false);
    }

}