<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;
class EditSFIPlan extends EditController
{

    public function getModelClassName()
    {
        return 'SFIPlan';
    }
}