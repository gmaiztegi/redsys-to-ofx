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
     * TransactionParser constructor.
     * @param RedsysStatementParser     $redsysStatementParser
     * @param SabadellConsignmentParser $consignmentParser
     * @param TransactionFlattener      $transactionFlattener
     */
    public function __construct(RedsysStatementParser $redsysStatementParser, SabadellConsignmentParser $consignmentParser, TransactionFlattener $transactionFlattener)
    {
        $this->redsysStatementParser = $redsysStatementParser;
        $this->consignmentParser = $consignmentParser;
        $this->transactionFlattener = $transactionFlattener;
    }

    /**
     * @param File $consignmentFile
     * @param File $transactionFile
     *
     * @return Transaction[]
     */
    public function parseTransactionList(File $consignmentFile, File $transactionFile)
    {
        /** @var \PHPExcel_Reader_Excel5 $reader */
        $reader = \PHPExcel_IOFactory::createReader('Excel5');
        $consignmentSheet = $reader->load($consignmentFile->getPathname())->getActiveSheet();
        $consignmentData = $this->consignmentParser->parseConsignmentFile($consignmentSheet);

        $consignmentFinder = new ConsignmentFinder($consignmentData);

        /** @var \PHPExcel_Reader_CSV $reader */
        $reader = \PHPExcel_IOFactory::createReader('CSV');
        $reader->setInputEncoding('ISO-8859-1');
        $reader->setDelimiter(';');
        $transactionSheet = $reader->load($transactionFile->getPathname())->getActiveSheet();
        $transactionData = $this->redsysStatementParser->parse($transactionSheet, $consignmentFinder);

        return $this->transactionFlattener->flatten($transactionData);
    }
}
