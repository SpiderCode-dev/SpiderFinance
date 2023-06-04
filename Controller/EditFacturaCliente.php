<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Base\Calculator;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Core\Model\ReciboCliente;
use FacturaScripts\Plugins\SpiderFact\Lib\Constans;
use Symfony\Component\VarDumper\Cloner\Data;

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
            $totalAbonos = $model->getTotalAbonos();
            $receipts = $model->getReceipts();

            //Traer el total de pagos de los recibos
            $totalPayedReceipts = 0;
            $unPayedReceipts = [];
            foreach ($receipts as $receipt) {
                if ($receipt->pagado) {
                    $totalPayedReceipts += $receipt->importe;
                } else {
                    $unPayedReceipts[] = $receipt;
                }
            }

            // Calcular el saldo según el total de abonos
            $saldo = $model->total - $totalPayedReceipts - $totalAbonos;
            if ($saldo == 0) {
                $model->pagado = true;
                $model->save();
                ToolBox::i18nLog()->notice('Documento pagado correctamente');
                return false;
            }

            $planLine = $model->getPlanLine();
            if (!$planLine) {
                $this->dataBase->rollback();
                ToolBox::i18nLog()->warning('No se ha encontrado ninguna linea de plan de pagos');
                return false;
            }

            // Si la línea no tiene iva y es la única línea en la factura
            // TODO: Implementar para multiples líneas y con iva
            if ($planLine->iva == 0) {
                $planLine->pvpunitario = $totalAbonos;
                $planLine->cantidad = 1;
                $planLine->descripcion .= " - Pago ajustado total abonos: $ {$totalAbonos}";
                $model->total = $totalAbonos;
                $model->observaciones .= "\n El próximo documento emitido tendrá un recargo de $ {$saldo} por ajuste de abonos.";
                if (!$planLine->save()) {
                    $this->dataBase->rollback();
                    ToolBox::i18nLog()->warning('No se ha podido guardar la linea de plan de pagos');
                    return;
                }
                $lines = $model->getLines();
                Calculator::calculate($model, $lines, false);
                if (!$model->save()) {
                    $this->dataBase->rollback();
                    ToolBox::i18nLog()->warning('No se ha podido guardar la factura');
                    return;
                }

                $this->dataBase->commit();
                ToolBox::i18nLog()->notice('Documento pagado correctamente');

                $receipt = $model->getReceipts();
                foreach ($receipt as $receipt) {
                    $receipt->pagado = true;
                    $receipt->nick = $this->user->nick;
                    $receipt->save();
                }
                return;
            }
            $this->dataBase->rollback();
        }
        return parent::execPreviousAction($action);
    }


    public function loadData($viewName, $view)
    {
        parent::loadData($viewName, $view);
        if ($viewName == 'ListAbono') {
            $model = $this->getModel();
            $where = [
                new DataBaseWhere('iddocument', $model->idfactura),
                new DataBaseWhere('typedoc', Constans::className($model))
            ];

            $view->loadData('', $where);
        }
    }

}
