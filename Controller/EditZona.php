<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;
class EditZona extends EditController
{

    public function getModelClassName(): string
    {
        return 'Zona';
    }
}