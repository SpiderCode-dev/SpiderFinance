<?php

namespace FacturaScripts\Plugins\SpiderFinance\Model;

use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;

class ClienteInstalacion extends ModelClass
{
    use ModelTrait;

    public $id;

    const STATUS_REGISTER = 0;
    const STATUS_PENDING = 1;
    const STATUS_INSTALLED = 2;
    const STATUS_CANCELLED = 3;

    public function __get($name)
    {
        $getter_name = "get" . $name;
        if (method_exists($this, $getter_name)) {
            return $this->{$getter_name}();
        }
        return null;
    }

    public static function primaryColumn(): string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'sfi_cliente_instalaciones';
    }

    public function getStatusName() {
        if ($this->exists()) {
            return $this->statusName();
        }
        return 'Registro';
    }

    public function statusName() {

        switch ($this->status) {
            case self::STATUS_REGISTER:
                return 'Registro';
            case self::STATUS_PENDING:
                return 'Pendiente';
            case self::STATUS_INSTALLED:
                return 'Instalado';
            case self::STATUS_CANCELLED:
                return 'Cancelado';
            default:
                return 'Desconocido';
        }
    }
}