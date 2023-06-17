<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Model\DocRecurringSale;
use FacturaScripts\Plugins\SpiderFinance\Model\ClienteInstalacion;

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
        $this->createDocumentsView();
        $this->showButtons();
    }

    public function execPreviousAction($action)
    {
        if ($action == 'setup-install') {
            $model = $this->getModel();
            $model->loadFromCode($this->request->query->get('code'));
            if ($model->status != ClienteInstalacion::STATUS_PENDING &&
                $model->status != ClienteInstalacion::STATUS_REGISTER) {
                $this->toolBox()->i18nLog()->warning('Instalación ya configurada.');
                return;
            }

            $data = $this->request->request->all();
            $this->dataBase->beginTransaction();
            $created = $model->setupInstall($data);

            if (!$created) {
                $this->toolBox()->i18nLog()->warning('No se ha podido crear la instalación.');
                return;
            }

            $this->dataBase->commit();
            $this->toolBox()->i18nLog()->info('Instalación iniciada correctamente, configure los datos de mikrotik.');
        }

        if ($action == 'pending-install') {
            $model = $this->getModel();
            $model->loadFromCode($this->request->query->get('code'));
            $model->status = ClienteInstalacion::STATUS_PENDING;
            $model->save();
        }
        $this->createViews();
        return parent::execPreviousAction($action);
    }


    public function execAfterAction($action)
    {
        parent::execAfterAction($action);

        if ($action == 'insert' && $this->active == 'EditClienteInstalacion') {
            $model = $this->getModel();
            $data = $this->request->request->all();
            $this->dataBase->beginTransaction();
            $created = $model->init($data);

            if (!$created) {
                $model->delete();
                $this->dataBase->rollback();
                $this->toolBox()->i18nLog()->warning('No se ha podido crear la instalación.');
                return;
            }

            $this->dataBase->commit();
            $this->loadData($this->getMainViewName(), $this->views[$this->getMainViewName()]);
            $this->toolBox()->i18nLog()->info('Instalación creada correctamente, configure los documentos programados.');
        }
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
    }

    public function createDocumentsView($viewName = 'ListFacturaCliente') {
        $this->addListView($viewName, 'FacturaCliente', 'Pagos de cliente', 'fas fa-file-invoice');
        $this->views[$viewName]->addOrderBy(['fecha'], 'date', 2);
        $this->views[$viewName]->addFilterPeriod('fecha', 'date', 'fecha');
        $this->views[$viewName]->addFilterCheckbox('pagada', 'paid', 'pagada');
    }

    public function createLineasProgramadasView($viewName = 'ListLineaProgramada') {
        $this->addListView($viewName, 'LineaProgramada', 'Extras de instalación', 'fas fa-cart-plus');
    }

    public function loadData($viewName, $view)
    {
        parent::loadData($viewName, $view);
        $mainModel = $this->getModel();

        switch ($viewName) {
            case $this->getMainViewName():
                if ($mainModel->exists()  && $mainModel->idcontacto) {
                    $contact = (new Contacto())->get($mainModel->idcontacto);
                    $client = $contact->getCustomer();

                    $mainModel->cifnif = $client->cifnif;
                    $mainModel->nombrecliente = $client->razonsocial;
                    $mainModel->email = $contact->email;
                    $mainModel->telefono = $contact->telefono1;
                    $mainModel->direccion = $contact->direccion;

                    // TODO: Load Recurring doc
                    $recurring = new DocRecurringSale();
                    $exists = $recurring->loadFromCode('', [
                        new DataBaseWhere('id_installation', $mainModel->primaryColumnValue())
                    ]);
                    // TODO: Edit data update contact
                }
                break;

            case 'ListLineaProgramada':
            case 'ListDocRecurringSale':
            case 'ListFacturaCliente':
                $where = [new DataBaseWhere('id_installation', $mainModel->primaryColumnValue())];
                $view->loadData('', $where);
                break;

        }
    }

    public function showButtons() {
        $mvn = $this->getMainViewName();
        $model = $this->getModel();
        $model->loadFromCode($this->request->query->get('code'));
        if ($model->exists()) {
            if ($model->status == ClienteInstalacion::STATUS_REGISTER)
                $this->addButton($mvn, [
                    'action' => 'pending-install',
                    'icon' => 'fas fa-check',
                    'label' => 'Confirmar instalación',
                    'type' => 'action',
                    'color' => 'warning'
                ]);
            if ($model->status == ClienteInstalacion::STATUS_PENDING)
                $this->addButton($mvn, [
                    'action' => 'setup-install',
                    'icon' => 'fas fa-network-wired',
                    'label' => 'Iniciar instalación',
                    'type' => 'action',
                    'confirm' => true,
                    'color' => 'success'
                ]);
        }

    }
}