<?php

namespace FacturaScripts\Plugins\SpiderFinance\Extension\Controller;

use FacturaScripts\Core\Base\Calculator;
use FacturaScripts\Core\Base\ToolBox;

class EditFacturaCliente
{
    public function execPreviousAction()
    {
        return function ($action) {
            if ($action != 'pay-adjust') {
                return;
            }
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
        };
    }
}