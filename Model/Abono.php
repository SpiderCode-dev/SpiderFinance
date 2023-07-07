<?php

namespace FacturaScripts\Plugins\SpiderFinance\Model;

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Core\Model\Settings;

class Abono extends ModelClass
{
    public $id;
    public $importe;
    public $concept;
    public $observations;
    public $created_at;
    public $updated_at;
    public $codpago;
    public $iddocument;
    public $codcliente;
    public $typedoc;

    use ModelTrait;

    public static function primaryColumn(): string
    {
        return 'id';
    }
    public static function tableName(): string
    {
        return 'sfi_abonos';
    }

    public function clear()
    {
        parent::clear();
        $this->created_at = date('Y-m-d H:i:s');
        $this->codpago = AppSettings::get('default', 'codpago');
    }

    public function test()
    {
        if ($this->importe <= 0) {
            $this->toolBox()->i18nLog()->warning('El valor no puede ser menor o igual a 0');
            return false;
        }

        if (empty($this->created_at)) {
            $this->created_at = date('Y-m-d H:i:s');
        }

        return parent::test();
    }
}