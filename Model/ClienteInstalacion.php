<?php

namespace FacturaScripts\Plugins\SpiderFinance\Model;

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Model\DocRecurringSaleLine;
use FacturaScripts\Dinamic\Model\FacturaCliente;
use FacturaScripts\Dinamic\Model\LineaFacturaCliente;
use FacturaScripts\Plugins\DocumentosRecurrentes\Model\DocRecurringSale;

class ClienteInstalacion extends ModelClass
{
    use ModelTrait;

    public $id;
    public $val_installation;
    public $generate_document;
    public $closedate;

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
        $this->status = self::STATUS_INSTALLED;
        $customer = $this->getCustomer();
        if (!$customer) {
            return false;
        }

        $db = new DataBase();
        $db->beginTransaction();

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
        //Todo: Create document or line with installation value
        if ($this->val_installation > 0) {
            if ($this->generate_document) {
                //Create document
                $invoice = new FacturaCliente();
                $invoice->codcliente = $customer->codcliente;
                $invoice->cifnif = $customer->cifnif;
                $invoice->nombrecliente = $customer->nombre;
                $invoice->codpago = $data['codpago'];
                $invoice->codserie = $data['codserie'];
                $invoice->codalmacen = $data['codalmacen'];
                $invoice->fecha = date('Y-m-d');
                $invoice->id_installation = $this->id;
                if (!$invoice->save()) {
                    return false;
                }

                //Create line
                $line = new LineaFacturaCliente();
                $line->idfactura = $invoice->idfactura;
                $line->descripcion = "Valor de Instalación";
                $line->pvpunitario = $this->val_installation;
                $line->cantidad = 1;
                if (!$line->save()) {
                    return false;
                }
            } else {
                //Create line
                $line = new LineaProgramada();
                $line->id_installation = $this->id;
                $line->descripcion = "Valor de Instalación";
                $line->cantidad = 1;
                $line->pvpunitario = $this->val_installation;
                if (!$line->save()) {
                    return false;
                }
            }
        }

        $this->setupdate = date('Y-m-d H:i:s');
        if ($this->save()) {
            $db->commit();
            return true;
        }

        $db->rollBack();
        return false;
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
        $this->codagente = $data['codagente'];
        return $this->save();
    }

    public function getCustomer()
    {
        $customer = new Cliente();
        $customer->loadFromCode($this->codcliente);
        return $customer->codcliente ? $customer : null;
    }
}