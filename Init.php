<?php
namespace FacturaScripts\Plugins\SpiderFinance;

use FacturaScripts\Core\Base\InitClass;


class Init extends InitClass
{
    public function init()
    {
        $this->loadExtension(new Extension\Model\Producto());
        $this->loadExtension(new Extension\Model\FacturaCliente());
    }
    public function update()
    {
    }

}
