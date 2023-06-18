<?php

namespace FacturaScripts\Plugins\SpiderFinance\Model;

use FacturaScripts\Core\Model\Base\InvoiceLineTrait;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Core\Model\FacturaCliente;
use FacturaScripts\Dinamic\Model\Base\SalesDocumentLine;
use FacturaScripts\Dinamic\Model\FacturaCliente as DinFacturaCliente;

class LineaProgramada extends SalesDocumentLine
{
    public $id_installation;
    public $agregado;
    public $idfacturaold;
    public $fecha_ingresado;
    public $fecha_agregado;

    use ModelTrait;
    use InvoiceLineTrait;

    public static function primaryColumn(): string
    {
        return 'idlinea';
    }
    public static function tableName(): string
    {
        return 'sfi_lineas_programadas';
    }

    public function url(string $type = 'auto', string $list = 'List'): string
    {
        return ModelClass::url($type, $list);
    }

    public function documentColumn(): string
    {
        return 'idfactura';
    }

    public function getDocument(): DinFacturaCliente
    {
        $factura = new DinFacturaCliente();
        $factura->loadFromCode($this->idfactura);
        return $factura;
    }

    public function install(): string
    {
        // needed dependency
        new FacturaCliente();
        return parent::install();
    }

    public function test(): bool
    {
        // servido will always be 0 to prevent stock problems when removing rectified invoices
        $this->servido = 0.0;
        $this->fecha_ingresado = date('Y-m-d H:i:s');
        if (empty($this->idproducto)) {
            $this->idproducto = null;
        }
        return parent::test();
    }

    public function delete(): bool
    {
        if ($this->agregado) {
            return false;
        }

        return parent::delete();
    }
}