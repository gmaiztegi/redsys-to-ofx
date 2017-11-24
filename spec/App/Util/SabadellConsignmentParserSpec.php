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

use App\Util\FinderKeyCreator;
use App\Util\SabadellConsignmentParser;
use PhpSpec\ObjectBehavior;


class SabadellConsignmentParserSpec extends ObjectBehavior
{
    function let(FinderKeyCreator $finderKeyCreator)
    {
        $this->beConstructedWith($finderKeyCreator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SabadellConsignmentParser::class);
    }

    function it_parses_the_sheet(FinderKeyCreator $finderKeyCreator,
        \PHPExcel_Worksheet $sheet,
        \PHPExcel_Cell $a1Cell,
        \PHPExcel_Cell $a6Cell,
        \PHPExcel_Cell $b6Cell,
        \PHPExcel_Cell $c6Cell,
        \PHPExcel_Cell $d6Cell,
        \PHPExcel_Cell $h6Cell,
        \PHPExcel_Cell $a7Cell,
        \PHPExcel_Cell $b7Cell,
        \PHPExcel_Cell $c7Cell,
        \PHPExcel_Cell $d7Cell,
        \PHPExcel_Cell $h7Cell,
        \PHPExcel_Cell $a8Cell,
        \PHPExcel_Cell $a14Cell,
        \PHPExcel_Cell $b14Cell,
        \PHPExcel_Cell $c14Cell,
        \PHPExcel_Cell $d14Cell,
        \PHPExcel_Cell $h14Cell,
        \PHPExcel_Cell $a15Cell
    ) {
        $consignment1 = "1";
        $consignment2 = "2";

        $a1Cell->getValue()->willReturn("CONSULTA DE OPERACIONES LIQUIDADAS A COMERCIOS");
        $sheet->getCell('A1')->willReturn($a1Cell);

        $dateOne = new \DateTime("2017-01-01 15:15");
        $lastDigitsOne = "0987";
        $amountOne = 20.00;
        $a6Cell->getValue()->willReturn("01/01/2017");
        $b6Cell->getValue()->willReturn("15:15");
        $c6Cell->getValue()->willReturn("4591________0987");
        $d6Cell->getValue()->willReturn("20,00");
        $h6Cell->getValue()->willReturn($consignment1);
        $sheet->getCell("A6")->willReturn($a6Cell);
        $sheet->getCell("B6")->willReturn($b6Cell);
        $sheet->getCell("C6")->willReturn($c6Cell);
        $sheet->getCell("D6")->willReturn($d6Cell);
        $sheet->getCell("H6")->willReturn($h6Cell);

        $dateTwo = new \DateTime("2017-01-01 15:30");
        $lastDigitsTwo = "0989";
        $amountTwo = 10.00;
        $a7Cell->getValue()->willReturn("01/01/2017");
        $b7Cell->getValue()->willReturn("15:30");
        $c7Cell->getValue()->willReturn("4591________0989");
        $d7Cell->getValue()->willReturn("10,00");
        $h7Cell->getValue()->willReturn($consignment1);
        $sheet->getCell("A7")->willReturn($a7Cell);
        $sheet->getCell("B7")->willReturn($b7Cell);
        $sheet->getCell("C7")->willReturn($c7Cell);
        $sheet->getCell("D7")->willReturn($d7Cell);
        $sheet->getCell("H7")->willReturn($h7Cell);

        $a8Cell->getValue()->willReturn("Nº operaciones 2");
        $sheet->getCell("A8")->willReturn($a8Cell);

        $dateThree = new \DateTime("2017-01-02 16:00");
        $lastDigitsThree = "0988";
        $amountThree = 30.00;
        $a14Cell->getValue()->willReturn("02/01/2017");
        $b14Cell->getValue()->willReturn("16:00");
        $c14Cell->getValue()->willReturn("4591________0988");
        $d14Cell->getValue()->willReturn("30,00");
        $h14Cell->getValue()->willReturn($consignment2);
        $sheet->getCell("A14")->willReturn($a14Cell);
        $sheet->getCell("B14")->willReturn($b14Cell);
        $sheet->getCell("C14")->willReturn($c14Cell);
        $sheet->getCell("D14")->willReturn($d14Cell);
        $sheet->getCell("H14")->willReturn($h14Cell);

        $a15Cell->getValue()->willReturn("");
        $sheet->getCell("A15")->willReturn($a15Cell);

        $finderKeyCreator->createIndex($dateOne, $lastDigitsOne, $amountOne)->willReturn("keyone");
        $finderKeyCreator->createIndex($dateTwo, $lastDigitsTwo, $amountTwo)->willReturn("keytwo");
        $finderKeyCreator->createIndex($dateThree, $lastDigitsThree, $amountThree)->willReturn("keythree");

        $transactions = $this->parseConsignmentFile($sheet);
        $transactions->shouldHaveCount(3);
        $transactions["keyone"]->shouldBe(intval($consignment1));
        $transactions["keytwo"]->shouldBe(intval($consignment1));
        $transactions["keythree"]->shouldBe(intval($consignment2));
    }
}
