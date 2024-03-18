<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Base\Calculator;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Plugins\SpiderFact\Lib\FactTools;

class EditFacturaCliente extends \FacturaScripts\Core\Controller\EditFacturaCliente
{
    public function createViews()
    {
        $this->setTabsPosition('top');
        $this->createViewsDoc();
        $this->createAbonosView();
        $this->createViewsReceipts();
        $this->createViewDocFiles();
        //$this->createViewLogAudit();
        //$this->createViewsAccounting();
        //$this->createViewsRefunds();
    }

    public function createAbonosView($viewName = 'ListAbono')
    {
        $this->addListView($viewName, 'Abono', 'Abonos', 'fas fa-dollar-sign');
    }

    public function createViewsReceipts(string $viewName = 'ListReciboCliente')
    {
        $this->addListView($viewName, 'ReciboCliente', 'receipts', 'fas fa-dollar-sign');
        $this->views[$viewName]->addOrderBy(['vencimiento'], 'expiration');

        // buttons
        $this->addButton($viewName, [
            'action' => 'generate-receipts',
            'confirm' => 'true',
            'icon' => 'fas fa-magic',
            'label' => 'generate-receipts'
        ]);

        $this->addButton($viewName, [
            'action' => 'paid',
            'confirm' => 'true',
            'icon' => 'fas fa-check',
            'label' => 'paid'
        ]);

        // disable columns
        $this->views[$viewName]->disableColumn('customer');
        $this->views[$viewName]->disableColumn('invoice');

        // settings
        $this->setSettings($viewName, 'modalInsert', 'generate-receipts');
    }

    public function execPreviousAction($action)
    {
        if ($action == 'pay-adjust') {
            $this->dataBase->beginTransaction();
            $model = $this->getModel();

            //Traer el total de pagos de los recibos y abonos
            $totalAbonos = $model->getTotalAbonos();
            $totalPayedReceipts = $model->getTotalReceipts();
            $saldo = $model->total - $totalPayedReceipts - $totalAbonos;

            if ($saldo == 0 || ($totalAbonos == 0 && $saldo == $model->total)) {
                $this->payReceipts($model);
                ToolBox::i18nLog()->notice('Documento pagado correctamente');
                return;
            }

            $serie = $model->getSerie();
            if (!$serie->siniva) {
                ToolBox::i18nLog()->warning('No se puede ajustar el documento porque tiene iva');
                return;
            }

            $lines = $model->getLines();
            if (empty($lines)) {
                ToolBox::i18nLog()->warning('No se ha encontrado ninguna linea de plan de pagos');
                return false;
            }

            $newLine = $model->getNewLine();
            $newLine->pvpunitario = -$saldo;
            $newLine->descripcion .= " - Pago ajustado total abonos: $ {$totalAbonos}";
            $newLine->codimpuesto = null;
            $newLine->iva = 0;

            if ($newLine->save()) {
                $lines = $model->getLines();
                Calculator::calculate($model, $lines, false);
                $model->observaciones .= "\n El próximo documento emitido tendrá un recargo de $ {$saldo} por ajuste de abonos.";
                if (!$model->save()) {
                    $this->dataBase->rollback();
                    ToolBox::i18nLog()->warning('No se ha podido guardar la factura');
                    return;
                }

                $generated = $model->generateLineaProgramada($saldo);
                if (!$generated) {
                    $this->dataBase->rollback();
                    ToolBox::i18nLog()->warning('No se ha podido generar la linea programada para el nuevo documento');
                    return;
                }

                ToolBox::i18nLog()->notice('Documento pagado correctamente');
                $this->payReceipts($model);
                $this->dataBase->commit();
                $this->redirect($model->url('edit'));
                return true;
            }
            $this->dataBase->rollback();
        }
        return parent::execPreviousAction($action);
    }


    private function payReceipts($model) {
        $receipts = $model->getReceipts();
        foreach ($receipts as $receipt) {
            $receipt->pagado = true;
            $receipt->nick = $this->user->nick;
            $receipt->save();
        }
    }

    public function loadData($viewName, $view)
    {
        parent::loadData($viewName, $view);
        if ($viewName == 'ListAbono') {
            $model = $this->getModel();
            $where = [
                new DataBaseWhere('iddocument', $model->idfactura),
                new DataBaseWhere('typedoc', FactTools::className($model)),
                new DataBaseWhere('codcliente', $model->codcliente)
            ];

            $view->loadData('', $where);
        }
    }

}
