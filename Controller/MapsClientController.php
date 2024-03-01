<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Base\Controller;
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

    public function execAction($action)
    {
        switch ($action) {
            case 'get-clients-location':
                $this->getClientsLocation();
                break;
            default:
                break;
        }
    }

    public function getClientsLocation()
    {
        $this->setTemplate(false);
        $installations = (new ClienteInstalacion())->all();
        $response = [];
        foreach ($installations as $installation) {
            if (!empty($installation->coords))
                $response[] = $installation;
        }

        $this->response->setContent(json_encode($response));
    }
}