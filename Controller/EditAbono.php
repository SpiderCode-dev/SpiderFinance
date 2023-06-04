<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;
class EditAbono extends EditController
{

    public function getModelClassName()
    {
        return 'Abono';
    }
}