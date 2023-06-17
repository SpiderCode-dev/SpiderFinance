<?php

namespace FacturaScripts\Plugins\SpiderFinance\Extension\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Dinamic\Lib\AssetManager;
use FacturaScripts\Dinamic\Model\Base\DocRecurring;
use FacturaScripts\Dinamic\Model\Variante;

class EditDocRecurringSale
{
    public function createViews() {
        return function () {
            AssetManager::add('js', '/Dinamic/Assets/JS/DocRecurringSaleFi.js');
        };
    }

    public function execPreviousAction() {
        return function ($action) {
            if ($action == 'calculate-partials') {
                $this->calculatePartialsAction();
            }
        };
    }

    public function calculatePartialsAction()
    {
       return function () {
           $code = $this->request->query->get('code');
           $mainModel = $this->getModel()->get($code);
           if ($mainModel->termtype != DocRecurring::TERM_TYPE_MONTHS) {
               ToolBox::log()->warning('no-monthly-document');
               return;
           }

           $monthDays = $this->monthsInDates($mainModel->startdate, $mainModel->nextdate);
           if ($monthDays > 1) {
               ToolBox::log()->warning('period-dates-must-be-one-month');
               return;
           }

           $lines = $mainModel->getLines();
           if (empty($lines) || count($lines) > 1) {
                ToolBox::log()->warning('no-lines-or-more-than-one-line');
                return;
           }

           $variant = new Variante();
           $variant->loadFromCode('', [
               new DataBaseWhere('referencia', $lines[0]->reference)
           ]);

           if (!$variant->exists()) {
               ToolBox::log()->warning('variant-plan-not-found');
               return;
           }

           $vPlan = $variant->precio;
           $monthDays = (int) $this->currentMonthDays(strtotime($mainModel->startdate));
           $currentDay = intval(date('d', strtotime($mainModel->startdate)));
           $vPagar = ($vPlan * $currentDay) / $monthDays;

           if ($mainModel->firstdate) {
               $monthDays = (int) $this->currentMonthDays(strtotime($mainModel->firstdate));
               $currentDay = intval(date('d', strtotime($mainModel->firstdate)));
               $vPagar += ($vPlan * $currentDay) / $monthDays;
           }

           $percentage = ($vPagar / $vPlan) * 100;
           $mainModel->firstpct = round($percentage);
           $mainModel->firstvalue = round(($percentage / 100) * $vPlan, 2);
           $mainModel->save();
       };
    }


    public function currentMonthDays()
    {
       return function ($strTime = null) {
           $cMonth = intval(date('m', $strTime));
           $cYear = intval(date('Y', $strTime));
           //return \cal_days_in_month(CAL_GREGORIAN, $cMonth, $cYear);
           return date('t', mktime(0, 0, 0, $cMonth, 1, $cYear));
       };
    }

    public function monthsInDates()
    {
        return function ($startDate, $endDate) {
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($endDate);

            $diff = $startDate->diff($endDate);
            return ( $diff->y * 12 ) + $diff->m;
        };
    }
}