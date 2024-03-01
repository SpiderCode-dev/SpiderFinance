<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;
class EditCajaNap extends EditController
{

    public function getModelClassName(): string
    {
        return 'CajaNap';
    }
}