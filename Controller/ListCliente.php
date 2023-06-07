<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

class ListCliente extends \FacturaScripts\Core\Controller\ListCliente
{
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'customers';
        $data['title'] = 'customers';
        $data['icon'] = 'fas fa-users';
        return $data;
    }

}