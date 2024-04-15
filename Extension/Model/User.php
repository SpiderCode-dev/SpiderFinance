<?php

namespace FacturaScripts\Plugins\SpiderFinance\Extension\Model;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\Agente;

class User
{
    public function saveInsert()
    {
        return function(array $values = []) {
            if (empty($this->codagente)) {
                $codagente = Tools::settings('finances', 'codagente' ?? "", null);
                if(empty($codagente)){
                    $agente = new Agente();
                    $agente->loadFromCode(null,[new DataBaseWhere('codagente',null,'!=')],['codagente' => 'ASC']);
                    $codagente = $agente->codagente;
                }
                $this->codagente = $codagente;
            }
            parent::saveInsert($values);
        };
    }
}