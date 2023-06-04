<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

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

    public function loadData($viewName, $view)
    {
        parent::loadData($viewName, $view);
        $model = $view->model;
        if ($model->exists()) {
            $contact = (new Contacto())->get($model->idcontacto);
            $client = (new Cliente())->get($contact->codcliente);

            $model->cifnif = $client->cifnif;
            $model->nombrecliente = $client->razonsocial;
            $model->email = $contact->email;
            $model->telefono = $contact->telefono1;
            $model->direccion = $contact->direccion;
        }
    }


}