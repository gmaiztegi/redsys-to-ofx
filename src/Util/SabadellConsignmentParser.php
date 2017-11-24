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

use App\Exception\InvalidStatementException;

/**
 * Class ConsignmentParser
 *
 * @author Gorka Maiztegi <gmaiztegi@gmail.com>
 */
class SabadellConsignmentParser
{
    /**
     * @var FinderKeyCreator
     */
    protected $finderKeyCreator;

    /**
     * SabadellConsignmentParser constructor.
     * @param FinderKeyCreator $finderKeyCreator
     */
    public function __construct(FinderKeyCreator $finderKeyCreator)
    {
        $this->finderKeyCreator = $finderKeyCreator;
    }

    /**
     * @param \PHPExcel_Worksheet $sheet
     *
     * @return array
     *
     * @throws InvalidStatementException
     */
    public function parseConsignmentFile(\PHPExcel_Worksheet $sheet)
    {
        if (0 !== strpos($sheet->getCell('A1')->getValue(), "CONSULTA DE OPERACIONES LIQUIDADAS A COMERCIOS")) {
            throw new InvalidStatementException("The spreadsheet doesn't seem to be a valid consignment report.");
        }

        $currentRow = 6;
        $transactions = array();

        do {
            $firstColumn = $sheet->getCell('A'.$currentRow)->getValue();

            $matches = array();

            if (1 === preg_match("/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/", $firstColumn, $matches)) {
                $date = $sheet->getCell('A'.$currentRow)->getValue();
                $time = $sheet->getCell('B'.$currentRow)->getValue();
                $timestamp = \DateTime::createFromFormat('d/m/Y H:i', $date.' '.$time);
                $lastDigits = sscanf($sheet->getCell('C'.$currentRow)->getValue(), '%4s________%4s')[1];
                $amount = floatval(str_replace(',', '.', $sheet->getCell('D'.$currentRow)->getValue()));
                $consignment = intval($sheet->getCell('H'.$currentRow)->getValue());

                $key = $this->finderKeyCreator->createIndex($timestamp, $lastDigits, $amount);

                $transactions[$key] = $consignment;

                ++$currentRow;
            } elseif (preg_match("/Nº operaciones ([0-9]+)/", $firstColumn, $matches)) {
                $currentRow += 6;
            } elseif (empty($firstColumn)) {
                break;
            } else {
                throw new InvalidStatementException(sprintf("Spreadsheet doesn't match format in row number %d.", $currentRow));
            }
        } while (true);

        return $transactions;
    }
}
