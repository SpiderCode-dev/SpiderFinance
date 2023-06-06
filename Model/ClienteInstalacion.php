<?php

namespace FacturaScripts\Plugins\SpiderFinance\Model;

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Model\DocRecurringSaleLine;
use FacturaScripts\Plugins\DocumentosRecurrentes\Model\DocRecurringSale;

class ClienteInstalacion extends ModelClass
{
    use ModelTrait;

    public $id;

    const STATUS_REGISTER = 0;
    const STATUS_PENDING = 1;
    const STATUS_INSTALLED = 2;
    const STATUS_CANCELLED = 3;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        if (!$this->exists()) {
            $this->status = self::STATUS_REGISTER;
            $this->codpago = AppSettings::get('default', 'codpago');
            $this->codserie = AppSettings::get('default', 'codserie');
            $this->codalmacen = AppSettings::get('default', 'codalmacen');
        }
    }

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

    public function setupInstall($data) {
        if (!$this->exists()) {
            ToolBox::log()->warning('La instalación no existe, guarde los datos para ejecutar esta acción.');
            return false;
        }

        $this->loadFromData($data);
        if (!$this->save()) {
            return false;
        }

        //Use customer
        $this->status = self::STATUS_PENDING;
        $customer = $this->getCustomer();
        if (!$customer) {
            return false;
        }

        //Create recurring doc
        $doc = new DocRecurringSale();
        $doc->codpago = $data['codpago'];
        $doc->codserie = $data['codserie'];
        $doc->codalmacen = $data['codalmacen'];
        $doc->startdate = date('Y-m-d');
        $doc->coddivisa = AppSettings::get('default', 'coddivisa');
        $doc->codcliente = $customer->codcliente;
        $doc->generatedoc = 'FacturaCliente';
        $doc->name = "Instalación " . $this->id;
        $doc->id_installation = $this->id;
        if (!$doc->save()) {
            return false;
        }

        //Create recurring line doc with plan value
        $plan = new Plan();
        $plan->loadFromCode($data['idplan']);
        $product = $plan->getProduct();

        $line = new DocRecurringSaleLine();
        $line->iddoc = $doc->id;
        $line->idproduct = $product->idproducto;
        $line->reference = $product->referencia;
        $line->quantity = 1;
        $line->price = $plan->price;
        if (!$line->save()) {
            return false;
        }

        return $this->save();
    }

    public function init($data)
    {
        //Create contact
        $contacto = new Contacto();
        $contacto->nombre = $data['nombrecliente'];
        $contacto->cifnif = $data['cifnif'];
        $contacto->telefono1 = $data['telefono'];
        $contacto->email = $data['email'];
        $contacto->direccion = $data['direccion'];
        if (!$contacto->save()) {
            return false;
        }

        //Create customer
        $customer = $contacto->getCustomer(true);
        if (!$customer->save()) {
            return false;
        }

        $this->codcliente = $customer->codcliente;
        $this->idcontacto = $contacto->idcontacto;

        return $this->save();
    }

    public function getCustomer()
    {
        $customer = new Cliente();
        $customer->loadFromCode($this->codcliente);
        return $customer->codcliente ? $customer : null;
    }
}