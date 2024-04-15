<?php

namespace FacturaScripts\Plugins\SpiderFinance\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Dinamic\Model\Agente;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Model\DocRecurringSaleLine;
use FacturaScripts\Dinamic\Model\FacturaCliente;
use FacturaScripts\Dinamic\Model\LineaFacturaCliente;
use FacturaScripts\Plugins\DocumentosRecurrentes\Model\DocRecurringSale;

class ClienteInstalacion extends ModelClass implements \JsonSerializable
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

    public function getStatusName()
    {
        if ($this->exists()) {
            return $this->statusName();
        }
        return 'Registro';
    }

    public function statusName()
    {
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

    public function setupInstall($data)
    {
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
        $doc->codagente = $data["codagente"];
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
                $invoice->idcontactofact = $this->idcontacto;
                $invoice->codalmacen = $data['codalmacen'];
                $invoice->fecha = $data['setupdate'];
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
        $client = new Cliente();
        $found = $client->loadFromCode('', [
            new DataBaseWhere('cifnif', $data['cifnif'])
        ]);

        $contacto = new Contacto();
        $contacto->nombre = $data['nombrecliente'];
        $contacto->cifnif = $data['cifnif'];
        $contacto->telefono1 = $data['telefono'];
        $contacto->email = $data['email'];
        $contacto->direccion = $data['direccion'];

        if ($found) {
            $contacto->codcliente = $client->codcliente;
        }
        if (!$contacto->save()) {
            return false;
        }

        //Create customer
        $customer = $contacto->getCustomer(!$found);
        if (!$customer->save()) {
            return false;
        }

        $this->codcliente = $customer->codcliente;
        $this->idcontacto = $contacto->idcontacto;
        $this->codagente = $data['codagente'];
        return $this->save();
    }

    public function updateContact(array $data)
    {
        $contact = $this->getContact();
        $contact->nombre = $data['nombrecliente'];
        $contact->descripcion = $data['nombrecliente'];
        $contact->cifnif = $data['cifnif'];
        $contact->telefono1 = $data['telefono'];
        $contact->email = $data['email'];
        $contact->direccion = $data['direccion'];

        return $contact->save();
    }

    public function updatePayment(array $data)
    {
        $document = $this->getCurrentDoc();
        if ($document->exists()) {
            $document->codpago = $data['codpago'];
            $document->codagente = $data['codagente'];
            $document->codserie = $data['codserie'];
            $document->codalmacen = $data['codalmacen'];
            return $document->save();
        }
        return false;
    }

    public function getPlan()
    {
        $plan = new Plan();
        $plan->loadFromCode('', [new DataBaseWhere('id', $this->idplan)]);
        return $plan->id ? $plan : null;
    }

    public function getContact()
    {
        $contact = new Contacto();
        $contact->loadFromCode($this->idcontacto);
        return $contact->idcontacto ? $contact : null;
    }

    public function getCustomer()
    {
        $customer = new Cliente();
        $customer->loadFromCode($this->codcliente);
        return $customer->codcliente ? $customer : null;
    }

    public function getCajaNap()
    {
        $caja = new CajaNap();
        $caja->loadFromCode('', [new DataBaseWhere('id', $this->idnap)]);
        return $caja->id ? $caja : null;
    }

    public function getCurrentDoc()
    {
        $doc = new DocRecurringSale();
        $doc->loadFromCode('', [new DataBaseWhere('id_installation', $this->id)]);
        return $doc;
    }

    public function getInCharge()
    {
        $agent = new Agente();
        $agent->loadFromCode('', [new DataBaseWhere('codagente', $this->in_charge)]);
        return $agent->codagente ? $agent : null;
    }

    public function delete()
    {
        if ($this->status != static::STATUS_CANCELLED) {
            $this->status = static::STATUS_CANCELLED;
            if ($this->save()) {
                $installation = $this->getCurrentDoc();
                $installation->delete();

                $extras = (new LineaProgramada())->all([new DataBaseWhere('id_installation', $this->id)]);
                foreach ($extras as $extra) {
                    $extra->delete();
                }
            }
        }
        return parent::delete();
    }

    public function jsonSerialize()
    {
        $coords = explode(',', $this->coords);
        $contact = $this->getContact();
        return [
            'id' => $this->id,
            'url' => $this->url(),
            'lat' => doubleval(trim($coords[0])),
            'lng' => doubleval(trim($coords[1])),
            'nombre' => $contact->nombre,
            'cifnif' => $contact->cifnif,
            'email' => $contact->email,
            'plan' => $this->getPlan()->name,
            'direccion' => $contact->direccion,
            'telefono' => $contact->telefono1,
        ];
    }
}