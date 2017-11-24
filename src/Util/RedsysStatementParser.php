<?php

/*
 * This file is part of the Redsys to OFX package.
 *
 * (c) Gorka Maiztegi <gmaiztegi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Util;

use App\Entity\Transaction;
use App\Exception\InvalidStatementException;

/**
 * Class RedsysStatementParser
 *
 * @author Gorka Maiztegi <gmaiztegi@gmail.com>
 */
class RedsysStatementParser
{
    /**
     * @param \PHPExcel_Worksheet $sheet
     * @param ConsignmentFinder   $consignmentFinder
     *
     * @return Transaction[][]
     *
     * @throws InvalidStatementException If data is not acceptable in some way.
     */
    public function parse(\PHPExcel_Worksheet $sheet, ConsignmentFinder $consignmentFinder)
    {
        $transactions = array();
        $currentRow = 2;

        while (!empty($sheet->getCell('A'.$currentRow)->getValue())) {
            $date = \DateTime::createFromFormat('d/m/Y H:i:s', $sheet->getCell('A'.$currentRow)->getValue());
            $orderNumber = $sheet->getCell('D'.$currentRow)->getValue();
            list($success, $code) = sscanf($sheet->getCell('E'.$currentRow)->getValue(), '%s %s');
            $amount = sscanf($sheet->getCell('F'.$currentRow)->getValue(), '%f EUR')[0];
            $type = $sheet->getCell('C'.$currentRow)->getValue();
            $amount = "Devolución" === $type ? -$amount : $amount;
            list($firstDigits, $lastDigits) = sscanf($sheet->getCell('H'.$currentRow)->getValue(), '%d******%4s');

            if ("Autorizada" !== $success) {
                ++$currentRow;
                continue;
            }


            if ("3" === strval($firstDigits)[0]) {
                $consignment = 'AMEX'.$date->format('Ymd');
            } else {
                $consignment = $consignmentFinder->findConsignment($date, $lastDigits, $amount);
            }

            if (!$consignment) {
                throw new InvalidStatementException(sprintf("Consignment not found for order %s.", $orderNumber));
            }

            $transaction = new Transaction();
            $transaction->setDate($date);
            $transaction->setAmount($amount);
            $transaction->setCardNumberLast($lastDigits);
            $transaction->setCode($code);
            $transaction->setOrderNumber($orderNumber);

            if (!isset($transactions[$consignment])) {
                $transactions[$consignment] = array();
            }

            $transactions[$consignment][] = $transaction;

            ++$currentRow;
        }

        return $transactions;
    }
}
