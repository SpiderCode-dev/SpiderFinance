<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\PanelController;
use FacturaScripts\Dinamic\Model\FacturaCliente;

class RegistrarPago extends PanelController
{
    public static $codcliente;

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'customers';
        $data['icon'] = 'fas fa-cash-register';
        $data['title'] = 'Registrar Pago';
        return $data;
    }
    protected function createViews()
    {
        $this->addFindClient();
        $this->addPendingPayments();
        $this->setTabsPosition('bottom');
    }

    public function addFindClient($viewName = 'BuscarCliente')
    {
        $this->addEditView($viewName, 'Cliente', 'search', 'fas fa-search');
        $this->removeButtonsViewName($viewName);
    }

    public function addPendingPayments($viewName = 'ListFacturaCliente')
    {
        $this->addListView($viewName, 'FacturaCliente', 'payments', 'fas fa-receipt');
        $this->addButton($viewName, [
            'action' => 'pay-selected-receipts',
            'icon' => 'fas fa-money-bill-wave',
            'label' => 'Pagar seleccionadas',
            'confirm' => 'true',
            'color' => 'success',
            'type' => 'action'
        ]);
        $this->removeButtonsViewName($viewName);
    }

    private function removeButtonsViewName($viewName)
    {
        $this->setSettings($viewName, 'btnNew', false);
        $this->setSettings($viewName, 'btnDelete', false);
        $this->setSettings($viewName, 'btnUndo', false);
        $this->setSettings($viewName, 'btnSave', false);
    }

    public function execPreviousAction($action)
    {
        if ($action == 'pay-selected-receipts') {
            $this->payInvoiceAction();
        }

        if ($action == 'search-customer') {
            static::$codcliente = $this->request->request->get('codcliente');
        }

        $this->views['BuscarCliente']->model->loadFromCode(static::$codcliente);
        $this->loadPayments($this->views['ListFacturaCliente']);
        return parent::execPreviousAction($action);
    }

    /**
     * This method pay the selected invoices
     * @return false|void
     */
    protected function payInvoiceAction()
    {
        $invoicesCodes = $this->request->request->get('code');
        foreach ($invoicesCodes as $code) {
            $model = (new FacturaCliente())->get($code);
            static::$codcliente = $model->codcliente;
            $receipts = $model->getReceipts();
            if (empty($receipts)) {
                self::toolBox()::i18nLog()->warning('invoice-has-no-receipts');
                return false;
            }

            foreach ($receipts as $receipt) {
                $receipt->nick = $this->user->nick;
                $receipt->pagado = true;
                if (false === $receipt->save()) {
                    return false;
                }
            }
        }

        if ($invoicesCodes) {
            self::toolBox()::i18nLog()->notice('receipts-paid-correctly');
        }
    }

    protected function loadData($viewName, $view)
    {
        if ($viewName == 'ListFacturaCliente') {
            $this->loadPayments($view);
        }
    }

    /**
     * This method load the payments of the customer
     * @param $view
     * @return void
     */
    public function loadPayments($view)
    {
        if (empty(static::$codcliente)) {
            return;
        }

        $view->loadData('', [
            new DataBaseWhere('codcliente', static::$codcliente),
            new DataBaseWhere('pagada', false)
        ]);
    }
}