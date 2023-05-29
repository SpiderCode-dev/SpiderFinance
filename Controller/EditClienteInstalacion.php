<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;
class EditClienteInstalacion extends EditController
{

    public function getModelClassName()
    {
        return 'ClienteInstalacion';
    }
}