<?php
namespace FacturaScripts\Plugins\SpiderFinance;

use FacturaScripts\Core\Base\InitClass;
use FacturaScripts\Dinamic\Model\LineaFacturaCliente;


class Init extends InitClass
{
    public function init()
    {
        new LineaFacturaCliente();
        $this->loadExtension(new Extension\Model\Producto());
        $this->loadExtension(new Extension\Model\FacturaCliente());
        $this->loadExtension(new Extension\Controller\EditDocRecurringSale());
    }
    public function update()
    {
    }

}
