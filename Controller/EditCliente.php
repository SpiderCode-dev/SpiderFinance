<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Contacto;
use FacturaScripts\Plugins\SpiderFinance\Model\ClienteInstalacion;

class EditCliente extends \FacturaScripts\Core\Controller\EditCliente
{
    /**
     * Create views
     */
    protected function createViews()
    {
        $viewName = 'Edit' . $this->getModelClassName();
        $modelName = $this->getModelClassName();
        $title = $this->getPageData()['title'];
        $viewIcon = $this->getPageData()['icon'];

        $this->addEditView($viewName, $modelName, $title, $viewIcon);
        $this->setSettings($viewName, 'btnPrint', true);
        $this->createContactsView();
        $this->createInstallationsView();
        $this->createProgramedDocsView();

        if ($this->user->can('EditFacturaCliente')) {
            $this->createInvoiceView('ListFacturaCliente');
        }
        if ($this->user->can('EditAlbaranCliente')) {
            $this->createDocumentView('ListAlbaranCliente', 'AlbaranCliente', 'delivery-notes');
        }
        if ($this->user->can('EditPedidoCliente')) {
            $this->createDocumentView('ListPedidoCliente', 'PedidoCliente', 'orders');
        }
        if ($this->user->can('EditPresupuestoCliente')) {
            $this->createDocumentView('ListPresupuestoCliente', 'PresupuestoCliente', 'estimations');
        }


        $this->createEmailsView();
        $this->createViewDocFiles();
    }

    public function createInstallationsView()
    {
        $this->addEditListView('EditContactoInstalacion', 'ClienteInstalacion', 'installations', 'fas fa-wrench');
    }

    public function createProgramedDocsView()
    {
        $viewName = 'ListDocRecurringSale';
        $this->addListView($viewName, 'DocRecurringSale', 'recurring', 'fas fa-calendar-plus');
        $this->views[$viewName]->addSearchFields(['name']);
        $this->views[$viewName]->addOrderBy(['id'], 'code');
        $this->views[$viewName]->addOrderBy(['name'], 'description');
        $this->views[$viewName]->addOrderBy(['nextdate', 'name'], 'next-date', 1);
        $this->views[$viewName]->addOrderBy(['lastdate', 'name'], 'last-date');

        /// disable columns
        $this->views[$viewName]->disableColumn('customer');
    }

    public function createContactsView(string $viewName = 'EditDireccionContacto')
    {
        $this->addEditListView($viewName, 'Contacto', 'contacts', 'fas fa-address-book');
    }

    public function loadData($viewName, $view)
    {
        parent::loadData($viewName, $view);
        if ($viewName === 'EditContactoInstalacion') {
            $codcliente = $this->getViewModelValue('EditCliente', 'codcliente');

            $widget = $view->columnForField('idcontacto')->widget;
            $values = $this->codeModel->all('contactos', 'idcontacto', 'descripcion', false,
                [new DataBaseWhere('codcliente', $codcliente)]);
            $widget->setValuesFromCodeModel($values);

            $widget = $view->columnForField('idplan')->widget;
            $values = $this->codeModel->all('sfi_planes', 'id', 'name', false,
                [new DataBaseWhere('active', true)]);
            $widget->setValuesFromCodeModel($values);


            $where = [new DataBaseWhere('codcliente', $codcliente)];
            $view->loadData('', $where);
        }
    }
}