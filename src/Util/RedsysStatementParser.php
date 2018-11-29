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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Class RedsysStatementParser
 *
 * @author Gorka Maiztegi <gmaiztegi@gmail.com>
 */
class RedsysStatementParser
{
    const COLUMN_DATE = 'Fecha';
    const COLUMN_ORDER_NUMBER = 'Número de pedido';
    const COLUMN_RESULT = 'Resultado operación y código';
    const COLUMN_AMOUNT = 'Importe';
    const COLUMN_CURRENCY = 'Moneda';
    const COLUMN_AMOUNT_EUROS = 'Importe Euros';
    const COLUMN_TYPE = 'Tipo operación';
    const COLUMN_CARD_NUMBER = 'Nº Tarjeta';
    const COLUMN_PAYER_NAME = 'Titular';

    /**
     * @param Worksheet         $sheet
     * @param ConsignmentFinder $consignmentFinder
     *
     * @return Transaction[][]
     *
     * @throws InvalidStatementException If data is not acceptable in some way.
     */
    public function parse(Worksheet $sheet, ConsignmentFinder $consignmentFinder)
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

        while (!empty($getColumn(self::COLUMN_DATE, $currentRow))) {
            $date = \DateTime::createFromFormat('d/m/Y', $getColumn(self::COLUMN_DATE, $currentRow));
            $orderNumber = $getColumn(self::COLUMN_ORDER_NUMBER, $currentRow);
            list($success, $code) = sscanf($getColumn(self::COLUMN_RESULT, $currentRow), '%s %s');

            if ("Autorizada" !== $success) {
                ++$currentRow;
                continue;
            }

            $amount = floatval($getColumn(self::COLUMN_AMOUNT_EUROS, $currentRow));
            $originalAmount = floatval($getColumn(self::COLUMN_AMOUNT, $currentRow));
            $originalCurrency = $getColumn(self::COLUMN_CURRENCY, $currentRow);

            $payerName = $getColumn(self::COLUMN_PAYER_NAME, $currentRow);

            $type = $getColumn(self::COLUMN_TYPE, $currentRow);
            $amount = "Devolución" === $type ? -$amount : $amount;
            $originalAmount = "Devolución" === $type ? -$originalAmount : $originalAmount;
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
            $transaction->setPayerName($payerName);
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

    /**
     * @param Worksheet $sheet
     *
     * @return array
     *
     * @throws InvalidStatementException
     */
    private function parseHeader(Worksheet $sheet)
    {

        $mapping = array(
            self::COLUMN_DATE => '',
            self::COLUMN_ORDER_NUMBER => '',
            self::COLUMN_RESULT => '',
            self::COLUMN_AMOUNT => '',
            self::COLUMN_CURRENCY => '',
            self::COLUMN_AMOUNT_EUROS => '',
            self::COLUMN_TYPE => '',
            self::COLUMN_CARD_NUMBER => '',
            self::COLUMN_PAYER_NAME => '',
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
