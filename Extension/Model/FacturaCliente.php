<?php

namespace FacturaScripts\Plugins\SpiderFinance\Extension\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\Abono;
use FacturaScripts\Plugins\SpiderFact\Lib\Constans;

class FacturaCliente
{
    public function getAbonos() {
        return function() {
            $modelName = Constans::className($this);
            $where = [
                new DataBaseWhere('iddocument', $this->primaryColumnValue()),
                new DataBaseWhere('typedoc', $modelName)
            ];

            return (new Abono())->all($where);
        };
    }

    public function getTotalAbonos() {
        return function() {
            $abonos = $this->getAbonos();
            $total = 0;
            foreach ($abonos as $abono) {
                $total += $abono->importe;
            }
            return $total;
        };
    }

    public function getPlanLine() {
        return function () {
            $lines = $this->getLines();
            foreach ($lines as $line)
                if ($line->idplan) {
                    return $line;
                }

            return null;
        };
    }

    public function deleteUnpayedReceipts() {
        return function () {
            $receipts = $this->getReceipts();
            foreach ($receipts as $receipt) {
                if (!$receipt->pagado)
                    $receipt->delete();
            }
        };
    }
}