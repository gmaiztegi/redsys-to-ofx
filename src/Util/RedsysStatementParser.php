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
    const COLUMN_DATETIME = 'Fecha';
    const COLUMN_ORDER_NUMBER = 'Número de pedido';
    const COLUMN_RESULT = 'Resultado operación y código';
    const COLUMN_AMOUNT = 'Importe';
    const COLUMN_TYPE = 'Tipo operación';
    const COLUMN_CARD_NUMBER = 'Nº Tarjeta';

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

        $columnMapping = $this->parseHeader($sheet);

        /**
         * @param $column string
         * @param $row int
         *
         * @return mixed The value
         */
        $getColumn = function ($column, $row) use ($sheet, $columnMapping) {
            return $sheet->getCell($columnMapping[$column].$row)->getValue();
        };

        while (!empty($getColumn(self::COLUMN_DATETIME, $currentRow))) {
            $date = \DateTime::createFromFormat('d/m/Y H:i:s', $getColumn(self::COLUMN_DATETIME, $currentRow));
            $orderNumber = $getColumn(self::COLUMN_ORDER_NUMBER, $currentRow);
            list($success, $code) = sscanf($getColumn(self::COLUMN_RESULT, $currentRow), '%s %s');

            if ("Autorizada" !== $success) {
                ++$currentRow;
                continue;
            }

            $matches = array();
            $amountStr = $getColumn(self::COLUMN_AMOUNT, $currentRow);

            if (1 === preg_match("/^([0-9]+.[0-9]{2}) EUR(?: \([0-9]+.[0-9]{0,2} [A-Z]{3}\))?$/", $amountStr, $matches)) {
                $amount = floatval($matches[1]);
                $originalAmount = null;
                $originalCurrency = null;
            } elseif (1 === preg_match("/^([0-9]+.[0-9]{2}) ([A-Z]{3}) \(([0-9]+.?[0-9]{0,2})\)$/", $amountStr, $matches) && "EUR" !== $matches[2]) {
                $amount = floatval($matches[3]);
                $originalAmount = floatval($matches[1]);
                $originalCurrency = $matches[2];
            } else {
                throw new InvalidStatementException(sprintf("Invalid amount for order %s.", $orderNumber));
            }

            $type = $getColumn(self::COLUMN_TYPE, $currentRow);
            $amount = "Devolución" === $type ? -$amount : $amount;
            list($firstDigits, $lastDigits) = sscanf($getColumn(self::COLUMN_CARD_NUMBER, $currentRow), '%d******%4s');

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
            $transaction->setOriginalAmount($originalAmount);
            $transaction->setOriginalCurrency($originalCurrency);
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

    private function parseHeader(\PHPExcel_Worksheet $sheet) {

        $mapping = array(
            self::COLUMN_DATETIME => '',
            self::COLUMN_ORDER_NUMBER => '',
            self::COLUMN_RESULT => '',
            self::COLUMN_AMOUNT => '',
            self::COLUMN_TYPE => '',
            self::COLUMN_CARD_NUMBER => '',
        );

        foreach (range('A', $sheet->getHighestColumn()) as $columnKey) {
            $mapping[$sheet->getCell($columnKey.'1')->getValue()] = $columnKey;
        }

        foreach ($mapping as $key => $value) {
            if (empty($value)) {
                throw new InvalidStatementException(sprintf('Missing column "%s" in statement.', $key));
            }
        }

        return $mapping;
    }
}
