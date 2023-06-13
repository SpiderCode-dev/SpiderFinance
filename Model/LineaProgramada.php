<?php

namespace FacturaScripts\Plugins\SpiderFinance\Model;

use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\LineaFacturaCliente;

class LineaProgramada extends LineaFacturaCliente
{
    public $id_installation;
    public $agregado;
    public $fecha_ingresado;
    public $fecha_agregado;

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

    public function delete(): bool
    {
        if ($this->agregado) {
            return false;
        }

        return parent::delete();
    }
}