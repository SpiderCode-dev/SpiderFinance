<?php
namespace FacturaScripts\Plugins\SpiderFinance;

use FacturaScripts\Core\Base\InitClass;
use FacturaScripts\Dinamic\Model\Producto;
use FacturaScripts\Plugins\SpiderFinance\Model\CajaNap;
use FacturaScripts\Plugins\SpiderFinance\Model\ClienteInstalacion;
use FacturaScripts\Plugins\SpiderFinance\Model\LineaProgramada;
use FacturaScripts\Plugins\SpiderFinance\Model\Plan;
use FacturaScripts\Plugins\SpiderFinance\Model\Recurso;
use FacturaScripts\Plugins\SpiderFinance\Model\Zona;


class Init extends InitClass
{
    public function init()
    {
        $this->loadExtension(new Extension\Model\Producto());
        $this->loadExtension(new Extension\Model\FacturaCliente());
        $this->loadExtension(new Extension\Model\LineaFacturaCliente());
        $this->loadExtension(new Extension\Controller\EditDocRecurringSale());
    }
    public function update()
    {
        new Producto();
        new ClienteInstalacion();
        new Zona();
        new CajaNap();
        new Plan();
        new Recurso();
        new LineaProgramada();
    }

}
