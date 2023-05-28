<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListSFIPlan extends ListController
{

    protected function createViews()
    {
        $this->addView('ListSFIPlan', 'SFIPlan', 'sfi_planes', 'fas fa-users');
    }
}