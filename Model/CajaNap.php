<?php

namespace FacturaScripts\Plugins\SpiderFinance\Model;

use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;

class CajaNap extends ModelClass
{
    public $id;
    public $code;
    public $observations;
    public $address;
    public $idzone;
    public $district;
    public $number_nap;
    public $number_ports;
    public $available;
    public $busy;
    public $coords;



    use ModelTrait;

    public static function primaryColumn(): string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'sfi_cajas_nap';
    }

    public function test(): bool
    {
        $this->code = $this->getNewCode();
        $busy = 0;
        if (!empty($this->busy))
            $busy = count(explode(',', $this->busy));

        $this->available = $this->number_ports - $busy;
        return parent::test();
    }

    public function fillPort(int $number)
    {
        $busy = explode(',', $this->busy);
        $busy[] = $number;
        $this->busy = implode(',', $busy);
        $this->available = $this->number_ports - count($busy);
    }

    public function getNewCode()
    {
        $code = '';
        $code .= "Z".$this->idzone;
        $code .= $this->district;
        $code .= "N".$this->number_nap;
        return $code;
    }
}