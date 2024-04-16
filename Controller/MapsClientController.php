<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Dinamic\Model\CajaNap;
use FacturaScripts\Dinamic\Model\ClienteInstalacion;

class MapsClientController extends Controller
{
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['showonmenu'] = false;
        return $data;
    }

    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);
        $action = $this->request->query->get('action');
        $this->execAction($action);
    }
    
}