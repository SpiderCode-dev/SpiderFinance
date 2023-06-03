<?php

namespace FacturaScripts\Plugins\SpiderFinance\Model;

use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;

class CajaNap extends ModelClass
{
    public $id;
    public $name;
    public $observations;
    public $idzona;
    public $distrito;
    public $numero_nap;
    public $numero_puertos;
    public $disponibles;
    public $latitude;
    public $longitude;

    use ModelTrait;

    public static function primaryColumn(): string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'sfi_cajas_nap';
    }
}