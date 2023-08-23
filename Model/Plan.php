<?php

namespace FacturaScripts\Plugins\SpiderFinance\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Dinamic\Model\Producto;

class Plan extends ModelClass
{
    public $id;
    public $name;
    public $description;
    public $price;
    public $active;
    public $create_rules;
    public $router;
    public $reuse;
    public $kbps_up;
    public $kbps_down;
    public $limit_at;
    public $burst_limit;
    public $burst_time;
    public $burst_threshold;
    public $priority;
    public $address_list;
    public $parent;


    use ModelTrait;

    public static function primaryColumn(): string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'sfi_planes';
    }

    public function saveInsert(array $values = []): bool
    {
        if (parent::saveInsert($values) && $this->getProduct()) {
            return true;
        }

        return false;
    }

    public function getProduct()
    {
        $product = new Producto();
        $found = $product->loadFromCode('', [
            new DataBaseWhere('idplan', $this->id)
        ]);

        if (!$found) {
            $product->referencia = str_replace(' ', '', $this->name);
            $product->descripcion = $this->description;
            $product->precio = $this->price;
            $product->idplan = $this->id;
            $product->nostock = true;
            $product->ventasinstock = true;
            return $product->save() ? $product : false;
        }

        return $product;
    }

    public function primaryDescription()
    {
        return $this->name . ' - ' . $this->description;
    }
}