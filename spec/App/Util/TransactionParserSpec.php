<?php

/*
 * This file is part of the Redsys to OFX package.
 *
 * (c) Gorka Maiztegi <gmaiztegi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\App\Util;

use App\Util\ConsignmentFinder;
use App\Util\FinderKeyCreator;
use App\Util\RedsysStatementParser;
use App\Util\SabadellConsignmentParser;
use App\Util\TransactionFlattener;
use App\Util\TransactionParser;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;

class TransactionParserSpec extends ObjectBehavior
{
    function let(RedsysStatementParser $redsysStatementParser, SabadellConsignmentParser $consignmentParser, TransactionFlattener $transactionFlattener, FinderKeyCreator $finderKeyCreator, XlsReader $excelReader, CsvReader $csvReader)
    {
        $this->beConstructedWith($redsysStatementParser, $consignmentParser, $transactionFlattener, $finderKeyCreator, $excelReader, $csvReader);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TransactionParser::class);
    }

    function it_parses_the_files(RedsysStatementParser $redsysStatementParser, SabadellConsignmentParser $consignmentParser, TransactionFlattener $transactionFlattener, FinderKeyCreator $finderKeyCreator, XlsReader $excelReader, CsvReader $csvReader, File $consignmentFile, File $transactionFile, Spreadsheet $consignmentExcel, Worksheet $consignmentSheet, Spreadsheet $transactionExcel, Worksheet $transactionSheet)
    {
        $consignmentFilename = 'somefilename.xls';
        $consignmentFile->getPathname()->willReturn($consignmentFilename);
        $excelReader->setReadDataOnly(true)->shouldBeCalled();
        $excelReader->load($consignmentFilename)->willReturn($consignmentExcel);
        $consignmentExcel->getActiveSheet()->willReturn($consignmentSheet);
        $consignmentData = array('consignmentdata');
        $consignmentParser->parseConsignmentFile($consignmentSheet)->shouldBeCalled()->willReturn($consignmentData);

        $csvReader->setInputEncoding('ISO-8859-1')->shouldBeCalled();
        $csvReader->setDelimiter(';')->shouldBeCalled();
        $csvReader->setReadDataOnly(true)->shouldBeCalled();
        $transactionFilename = 'anotherfilename.csv';
        $transactionFile->getPathname()->willReturn($transactionFilename);
        $csvReader->load($transactionFilename)->shouldBeCalled()->willReturn($transactionExcel);
        $transactionExcel->getActiveSheet()->willReturn($transactionSheet);
        $finder = new ConsignmentFinder($consignmentData, $finderKeyCreator->getWrappedObject());
        $transactionData = array('transactiondata');
        $redsysStatementParser->parse($transactionSheet, $finder)->shouldBeCalled()->willReturn($transactionData);

        $flattenedData = array('flattened');
        $transactionFlattener->flatten($transactionData)->shouldBeCalled()->willReturn($flattenedData);

        $this->parseTransactionList($consignmentFile, $transactionFile)->shouldReturn($flattenedData);
    }
}
