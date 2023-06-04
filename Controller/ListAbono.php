<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListAbono extends ListController
{
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['showonmenu'] = false;
        return $data;
    }

    protected function createViews()
    {
        $this->addView('ListAbono', 'Abono', 'Abonos', 'fas fa-dollar-sign');
    }
}