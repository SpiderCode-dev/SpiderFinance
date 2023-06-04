<?php

namespace FacturaScripts\Plugins\SpiderFinance\Model;

use FacturaScripts\Core\Model\LineaFacturaCliente;

class LineaExtraRecurrente extends LineaFacturaCliente
{
    public $id_installation;
    public $agregado;
    public $fecha_ingresado;


    public static function primaryColumn(): string
    {
        return 'idlinea';
    }
}