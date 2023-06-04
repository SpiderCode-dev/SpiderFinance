<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\AssetManager;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Contacto;

class EditClienteInstalacion extends EditController
{
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['title'] = 'installation';
        return $data;

    }

    public function getModelClassName()
    {
        return 'ClienteInstalacion';
    }

    public function createViews()
    {
        parent::createViews();
        $this->createRecurrentView();
        $this->createRecursosView();
        $this->createLineasProgramadasView();
    }

    public function createRecurrentView($viewName = 'ListDocRecurringSale') {
        $this->addListView($viewName, 'DocRecurringSale', 'recurring', 'fas fa-calendar-plus');
        $this->views[$viewName]->disableColumn('customer');
        $this->setSettings($viewName, 'btnNew', false);
        $this->setSettings($viewName, 'btnDelete', false);
    }

    public function createRecursosView($viewName = 'ListProducto') {
        $this->addListView($viewName, 'Producto', 'Recursos');
        $this->views[$viewName]->disableColumn('customer');
        $this->setSettings($viewName, 'btnNew', false);
        $this->setSettings($viewName, 'btnDelete', false);
    }

    public function createLineasProgramadasView($viewName = 'ListLineaProgramada') {
        $this->addListView($viewName, 'LineaProgramada', 'Extras de instalación', 'fas fa-file-invoice');
    }

    public function loadData($viewName, $view)
    {
        parent::loadData($viewName, $view);
        $mainModel = $this->getModel();

        if ($viewName == $this->getMainViewName())
            if ($mainModel->exists()) {
                $contact = (new Contacto())->get($mainModel->idcontacto);
                $client = (new Cliente())->get($contact->codcliente);

                $mainModel->cifnif = $client->cifnif;
                $mainModel->nombrecliente = $client->razonsocial;
                $mainModel->email = $contact->email;
                $mainModel->telefono = $contact->telefono1;
                $mainModel->direccion = $contact->direccion;
            }

        if ($viewName == 'ListLineaProgramada') {
            $where = [new DataBaseWhere('id_installation', $mainModel->primaryColumnValue())];
            $view->loadData('', $where);
        }
    }




}