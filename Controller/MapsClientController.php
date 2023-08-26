<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Dinamic\Model\ClienteInstalacion;

class MapsClientController extends Controller
{
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
            $coords = explode(',', $installation->coords);
            if (count($coords) == 2)
            $response[] = [
                'id' => $installation->id,
                'lat' => doubleval(trim($coords[0])),
                'lng' => doubleval(trim($coords[1])),
            ];
        }

        $this->response->setContent(json_encode($response));
    }

}