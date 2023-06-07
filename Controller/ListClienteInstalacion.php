<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\ListController;
use FacturaScripts\Plugins\SpiderFinance\Model\ClienteInstalacion;

class ListClienteInstalacion extends ListController
{
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'customers';
        $data['title'] = 'installations';
        $data['icon'] = 'fas fa-wrench';
        return $data;
    }

    protected function createViews()
    {
        $this->addView('ListInstalacionRegister', 'ClienteInstalacion', 'Registradas', 'fas fa-book');
        $this->addView('ListInstalacionPending', 'ClienteInstalacion', 'Pendientes', 'fas fa-stopwatch');
        $this->addView('ListInstalacionInstalled', 'ClienteInstalacion', 'Instaladas', 'fas fa-ethernet');
        $this->addView('ListInstalacionCancelled', 'ClienteInstalacion', 'Canceladas', 'fas fa-ban');

        $views = [
            'ListInstalacionPending',
            'ListInstalacionInstalled',
            'ListInstalacionCancelled',
        ];

        foreach ($views as $view) {
            $this->setSettings($view, 'btnNew', false);
        }
    }

    public function loadData($viewName, $view)
    {
        switch ($viewName) {
            case 'ListInstalacionRegister':
                return $view->loadData('', [
                    new DataBaseWhere('status', ClienteInstalacion::STATUS_REGISTER)
                ]);
            case 'ListInstalacionPending':
                return $view->loadData('', [
                    new DataBaseWhere('status', ClienteInstalacion::STATUS_PENDING)
                ]);
            case 'ListInstalacionInstalled':
                return $view->loadData('', [
                    new DataBaseWhere('status', ClienteInstalacion::STATUS_INSTALLED)
                ]);
            case 'ListInstalacionCanceled':
                return $view->loadData('', [
                    new DataBaseWhere('status', ClienteInstalacion::STATUS_CANCELLED)
                ]);
        }
    }
}