<?php

namespace FacturaScripts\Plugins\SpiderFinance\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\AssetManager;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Dinamic\Model\Variante;

class EditLineaProgramada extends EditController
{

    public function getModelClassName()
    {
        return 'LineaProgramada';
    }

    public function createViews()
    {
        parent::createViews();
        AssetManager::add('js', '/Dinamic/Assets/JS/EditLineaProgramada.js');
    }

    /**
     * @throws \Exception
     */
    public function execPreviousAction($action)
    {
        if ($action == 'get-ref-data')
        {
            $this->setTemplate(false);
            $reference = $this->request->get('ref');
            $variant = new Variante();
            $variant->loadFromCode('', [
                new DataBaseWhere('referencia', $reference)
            ]);
            if (!$variant->exists()) {
                throw new \Exception('No se ha encontrado el producto con referencia ' . $reference);
            }

            $product = $variant->getProducto();
            $this->response->setContent(json_encode([
                'idproducto' => $product->idproducto,
                'pvpunitario' => $variant->precio,
                'descripcion' => $product->descripcion,
                'codimpuesto' => $product->codimpuesto,
            ]));
            return false;
        }
        return parent::execPreviousAction($action);
    }
}