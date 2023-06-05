<?php

namespace FacturaScripts\Plugins\SpiderFinance\Extension\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Dinamic\Model\Base\DocRecurring;
use FacturaScripts\Dinamic\Model\Variante;

class EditDocRecurringSale
{
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

           $monthDays = intval($this->currentMonthDays());
           $cDay = intval(date('d'));

           $percentage = 100 - ($cDay * 100) / $monthDays;
           $mainModel->firstpct = round($percentage);
           $mainModel->firstvalue = round(($percentage / 100) * $variant->precio, 2);
           $mainModel->save();
       };
    }


    public function currentMonthDays()
    {
       return function () {
           $cMonth = intval(date('m'));
           $cYear = intval(date('Y'));
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