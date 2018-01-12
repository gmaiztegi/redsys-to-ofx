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

use App\Exception\InvalidStatementException;
use App\Util\ConsignmentFinder;
use App\Util\RedsysStatementParser;
use PhpSpec\ObjectBehavior;

class RedsysStatementParserSpec extends ObjectBehavior
{
    function let(\PHPExcel_Worksheet $sheet,
        \PHPExcel_Cell $a1Cell,
        \PHPExcel_Cell $b1Cell,
        \PHPExcel_Cell $c1Cell,
        \PHPExcel_Cell $d1Cell,
        \PHPExcel_Cell $e1Cell,
        \PHPExcel_Cell $f1Cell,
        \PHPExcel_Cell $g1Cell,
        \PHPExcel_Cell $h1Cell,
        \PHPExcel_Cell $a2Cell,
        \PHPExcel_Cell $c2Cell,
        \PHPExcel_Cell $d2Cell,
        \PHPExcel_Cell $e2Cell,
        \PHPExcel_Cell $f2Cell,
        \PHPExcel_Cell $h2Cell,
        \PHPExcel_Cell $a3Cell,
        \PHPExcel_Cell $c3Cell,
        \PHPExcel_Cell $d3Cell,
        \PHPExcel_Cell $e3Cell,
        \PHPExcel_Cell $f3Cell,
        \PHPExcel_Cell $h3Cell,
        \PHPExcel_Cell $a4Cell
    ) {
        $a1Cell->getValue()->willReturn("Fecha");
        $b1Cell->getValue()->willReturn("Nº de terminal");
        $c1Cell->getValue()->willReturn("Tipo operación");
        $d1Cell->getValue()->willReturn("Número de pedido");
        $e1Cell->getValue()->willReturn("Resultado operación y código");
        $f1Cell->getValue()->willReturn("Importe");
        $g1Cell->getValue()->willReturn("Cierre de sesión");
        $h1Cell->getValue()->willReturn("Nº Tarjeta");

        $sheet->getCell('A1')->willReturn($a1Cell);
        $sheet->getCell('B1')->willReturn($b1Cell);
        $sheet->getCell('C1')->willReturn($c1Cell);
        $sheet->getCell('D1')->willReturn($d1Cell);
        $sheet->getCell('E1')->willReturn($e1Cell);
        $sheet->getCell('F1')->willReturn($f1Cell);
        $sheet->getCell('G1')->willReturn($g1Cell);
        $sheet->getCell('H1')->willReturn($h1Cell);
        $sheet->getHighestColumn()->willReturn('H');

        $sheet->getCell('A2')->willReturn($a2Cell);
        $sheet->getCell('C2')->willReturn($c2Cell);
        $sheet->getCell('D2')->willReturn($d2Cell);
        $sheet->getCell('E2')->willReturn($e2Cell);
        $sheet->getCell('F2')->willReturn($f2Cell);
        $sheet->getCell('H2')->willReturn($h2Cell);

        $sheet->getCell('A3')->willReturn($a3Cell);
        $sheet->getCell('C3')->willReturn($c3Cell);
        $sheet->getCell('D3')->willReturn($d3Cell);
        $sheet->getCell('E3')->willReturn($e3Cell);
        $sheet->getCell('F3')->willReturn($f3Cell);
        $sheet->getCell('H3')->willReturn($h3Cell);

        $a4Cell->getValue()->willReturn("");
        $sheet->getCell("A4")->willReturn($a4Cell);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RedsysStatementParser::class);
    }

    function it_parses_the_file(\PHPExcel_Worksheet $sheet,
        ConsignmentFinder $consignmentFinder,
        \PHPExcel_Cell $a2Cell,
        \PHPExcel_Cell $c2Cell,
        \PHPExcel_Cell $d2Cell,
        \PHPExcel_Cell $e2Cell,
        \PHPExcel_Cell $f2Cell,
        \PHPExcel_Cell $h2Cell,
        \PHPExcel_Cell $a3Cell,
        \PHPExcel_Cell $c3Cell,
        \PHPExcel_Cell $d3Cell,
        \PHPExcel_Cell $e3Cell,
        \PHPExcel_Cell $f3Cell,
        \PHPExcel_Cell $h3Cell,
        \PHPExcel_Cell $a4Cell
    ) {
        $consignment1 = "1";
        $dateString1 = "03/01/2017 11:10:13";
        $date1 = \DateTime::createFromFormat('d/m/Y H:i:s', $dateString1);
        $a2Cell->getValue()->willReturn($dateString1);
        $c2Cell->getValue()->willReturn("Autorización");
        $d2Cell->getValue()->willReturn("ES-11111");
        $e2Cell->getValue()->willReturn("Autorizada 123456");
        $f2Cell->getValue()->willReturn("16.00 EUR");
        $h2Cell->getValue()->willReturn("4589******1234");
        $consignmentFinder->findConsignment($date1, "1234", 16.00)->willReturn($consignment1);

        $consignment2 = "2";
        $dateString2 = "04/01/2017 11:12:13";
        $date2 = \DateTime::createFromFormat('d/m/Y H:i:s', $dateString2);
        $a3Cell->getValue()->willReturn($dateString2);
        $c3Cell->getValue()->willReturn("Autorización");
        $d3Cell->getValue()->willReturn("ES-22222");
        $e3Cell->getValue()->willReturn("Autorizada 987654");
        $f3Cell->getValue()->willReturn("29.69 EUR (27.48 GBP)");
        $h3Cell->getValue()->willReturn("4589******9876");
        $consignmentFinder->findConsignment($date2, "9876", 29.69)->willReturn($consignment2);

        $a4Cell->getValue()->willReturn("");
        $sheet->getCell("A4")->willReturn($a4Cell);

        $result = $this->parse($sheet, $consignmentFinder);

        $result->shouldHaveCount(2);
        $result[$consignment1][0]->getDate()->shouldBeLike($date1);
        $result[$consignment1][0]->getOrderNumber()->shouldBe("ES-11111");
        $result[$consignment1][0]->getCode()->shouldBe("123456");
        $result[$consignment1][0]->getAmount()->shouldBe(16.00);
        $result[$consignment1][0]->getOriginalAmount()->shouldBe(null);
        $result[$consignment1][0]->getOriginalCurrency()->shouldBe(null);
        $result[$consignment1][0]->getCardNumberLast()->shouldBe("1234");
        $result[$consignment2][0]->getDate()->shouldBeLike($date2);
        $result[$consignment2][0]->getOrderNumber()->shouldBe("ES-22222");
        $result[$consignment2][0]->getCode()->shouldBe("987654");
        $result[$consignment2][0]->getAmount()->shouldBe(29.69);
        $result[$consignment2][0]->getOriginalAmount()->shouldBe(null);
        $result[$consignment2][0]->getOriginalCurrency()->shouldBe(null);
        $result[$consignment2][0]->getCardNumberLast()->shouldBe("9876");
    }

    function it_parses_currencies_different_than_euros(\PHPExcel_Worksheet $sheet,
        ConsignmentFinder $consignmentFinder,
        \PHPExcel_Cell $a2Cell,
        \PHPExcel_Cell $c2Cell,
        \PHPExcel_Cell $d2Cell,
        \PHPExcel_Cell $e2Cell,
        \PHPExcel_Cell $f2Cell,
        \PHPExcel_Cell $h2Cell,
        \PHPExcel_Cell $a3Cell
    ) {
        $consignment1 = "1";
        $dateString1 = "03/01/2017 11:10:13";
        $date1 = \DateTime::createFromFormat('d/m/Y H:i:s', $dateString1);
        $a2Cell->getValue()->willReturn($dateString1);
        $c2Cell->getValue()->willReturn("Autorización");
        $d2Cell->getValue()->willReturn("ES-11111");
        $e2Cell->getValue()->willReturn("Autorizada 123456");
        $f2Cell->getValue()->willReturn("42.00 GBP (47.81)");
        $h2Cell->getValue()->willReturn("4589******1234");
        $consignmentFinder->findConsignment($date1, "1234", 47.81)->willReturn($consignment1);

        $a3Cell->getValue()->willReturn("");
        $sheet->getCell("A3")->willReturn($a3Cell);

        $result = $this->parse($sheet, $consignmentFinder);

        $result->shouldHaveCount(1);
        $result[$consignment1][0]->getDate()->shouldBeLike($date1);
        $result[$consignment1][0]->getOrderNumber()->shouldBe("ES-11111");
        $result[$consignment1][0]->getCode()->shouldBe("123456");
        $result[$consignment1][0]->getAmount()->shouldBe(47.81);
        $result[$consignment1][0]->getOriginalAmount()->shouldBe(42.00);
        $result[$consignment1][0]->getOriginalCurrency()->shouldBe("GBP");
        $result[$consignment1][0]->getCardNumberLast()->shouldBe("1234");
    }

    function it_throws_exception_when_column_is_missing(\PHPExcel_Worksheet $sheet,
        ConsignmentFinder $consignmentFinder,
        \PHPExcel_Cell $a1Cell) {

        $a1Cell->getValue()->willReturn('NotADate');

        $this->shouldThrow(new InvalidStatementException('Missing column "Fecha" in statement.'))->duringParse($sheet, $consignmentFinder);
    }
}
