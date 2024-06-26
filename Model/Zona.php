<?php

namespace FacturaScripts\Plugins\SpiderFinance\Model;

use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;

class Zona extends ModelClass
{
    use ModelTrait;

    public static function primaryColumn(): string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'sfi_zonas';
    }
}