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
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class TransactionParser
 *
 * @author Gorka Maiztegi <gmaiztegi@gmail.com>
 */
class TransactionParser
{
    /**
     * @var RedsysStatementParser
     */
    protected $redsysStatementParser;

    /**
     * @var SabadellConsignmentParser
     */
    protected $consignmentParser;

    /**
     * @var TransactionFlattener
     */
    protected $transactionFlattener;

    /**
     * @var FinderKeyCreator
     */
    protected $finderKeyCreator;

    /**
     * @var XlsReader
     */
    protected $excelReader;

    /**
     * @var CsvReader
     */
    protected $csvReader;

    /**
     * TransactionParser constructor.
     * @param RedsysStatementParser     $redsysStatementParser
     * @param SabadellConsignmentParser $consignmentParser
     * @param TransactionFlattener      $transactionFlattener
     * @param FinderKeyCreator          $finderKeyCreator
     * @param XlsReader                 $excelReader
     * @param CsvReader                 $csvReader
     */
    public function __construct(RedsysStatementParser $redsysStatementParser, SabadellConsignmentParser $consignmentParser, TransactionFlattener $transactionFlattener, FinderKeyCreator $finderKeyCreator, XlsReader $excelReader, CsvReader $csvReader)
    {
        $this->redsysStatementParser = $redsysStatementParser;
        $this->consignmentParser = $consignmentParser;
        $this->transactionFlattener = $transactionFlattener;
        $this->finderKeyCreator = $finderKeyCreator;
        $this->excelReader = $excelReader;
        $this->csvReader = $csvReader;
    }

    /**
     * @param File $consignmentFile
     * @param File $transactionFile
     *
     * @return Transaction[]
     */
    public function parseTransactionList(File $consignmentFile, File $transactionFile)
    {
        $this->excelReader->setReadDataOnly(true);
        $consignmentSheet = $this->excelReader->load($consignmentFile->getPathname())->getActiveSheet();
        $consignmentData = $this->consignmentParser->parseConsignmentFile($consignmentSheet);

        $consignmentFinder = new ConsignmentFinder($consignmentData, $this->finderKeyCreator);

        $this->csvReader->setInputEncoding('ISO-8859-1');
        $this->csvReader->setDelimiter(';');
        $this->csvReader->setReadDataOnly(true);
        $transactionSheet = $this->csvReader->load($transactionFile->getPathname())->getActiveSheet();
        $transactionData = $this->redsysStatementParser->parse($transactionSheet, $consignmentFinder);

        return $this->transactionFlattener->flatten($transactionData);
    }
}
