<?php

namespace FacturaScripts\Plugins\SpiderFinance\Lib\DocumentosRecurrentes;

use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Plugins\SpiderFact\Lib\Constans;
use FacturaScripts\Plugins\SpiderFinance\Model\LineaProgramada;

class DocRecurringManager extends \FacturaScripts\Plugins\DocumentosRecurrentes\Lib\DocumentosRecurrentes\DocRecurringManager
{
    protected function createDocument(): bool
    {
        $created = parent::createDocument();
        if ($created && Constans::className($this->document) === 'FacturaCliente') {
            $this->document->id_installation = $this->template->id_installation;
            return $this->document->save();
        }

        return $created;
    }

    protected function createDocumentLines(): bool
    {
        $created = parent::createDocumentLines();
        $dataBase = new DataBase();
        if ($created && Constans::className($this->document) === 'FacturaCliente') {
            $dataBase->beginTransaction();
            $pendingLine = new LineaProgramada();

            $fecha = date('Y-m-d');
            $primerDia = date('Y-m-01', strtotime($fecha));
            $ultimoDia = date('Y-m-t', strtotime($fecha));

            $lines = $pendingLine->all([
                new DataBaseWhere('id_installation', $this->template->id_installation),
                new DataBaseWhere('fecha_ingresado', $primerDia, '>='),
                new DataBaseWhere('fecha_ingresado', $ultimoDia, '<='),
                new DataBaseWhere('agregado', false)
            ]);

            foreach ($lines as $line) {
                $line->idfactura = $this->document->idfactura;
                $line->fecha_agregado = date('Y-m-d H:i:s');
                $line->agregado = true;
                if (!$line->save()) {
                    $dataBase->rollback();
                    return false;
                }

                $newLine = empty($line->reference)
                    ? $this->document->getNewLine()
                    : $this->document->getNewProductLine($line->reference);
                $newLine->iva = $line->iva;
                $newLine->cantidad = $line->cantidad;
                $newLine->pvpunitario = $line->pvpunitario;
                $newLine->codimpuesto = $line->codimpuesto;
                $newLine->descripcion = $line->descripcion;
                $newLine->referencia = $line->referencia;
                $newLine->idproducto = $line->idproducto;
                $newLine->idfactura = $this->document->idfactura;

                if (!$newLine->save()) {
                    $dataBase->rollback();
                    return false;
                }
            }
            $dataBase->commit();
            return true;
        }
        $dataBase->rollback();
        return $created;
    }
}

