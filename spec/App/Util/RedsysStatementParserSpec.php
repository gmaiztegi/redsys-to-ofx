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
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpSpec\ObjectBehavior;

class RedsysStatementParserSpec extends ObjectBehavior
{
    function let(Worksheet $sheet,
        Cell $a1Cell,
        Cell $b1Cell,
        Cell $c1Cell,
        Cell $d1Cell,
        Cell $e1Cell,
        Cell $f1Cell,
        Cell $g1Cell,
        Cell $h1Cell,
        Cell $i1Cell,
        Cell $j1Cell,
        Cell $k1Cell,
        Cell $a2Cell,
        Cell $c2Cell,
        Cell $d2Cell,
        Cell $e2Cell,
        Cell $f2Cell,
        Cell $g2Cell,
        Cell $h2Cell,
        Cell $j2Cell,
        Cell $k2Cell,
        Cell $a3Cell,
        Cell $c3Cell,
        Cell $d3Cell,
        Cell $e3Cell,
        Cell $f3Cell,
        Cell $g3Cell,
        Cell $h3Cell,
        Cell $j3Cell,
        Cell $k3Cell,
        Cell $a4Cell
    ) {
        $a1Cell->getValue()->willReturn("Fecha");
        $b1Cell->getValue()->willReturn("Nº de terminal");
        $c1Cell->getValue()->willReturn("Tipo operación");
        $d1Cell->getValue()->willReturn("Número de pedido");
        $e1Cell->getValue()->willReturn("Resultado operación y código");
        $f1Cell->getValue()->willReturn("Importe");
        $g1Cell->getValue()->willReturn("Moneda");
        $h1Cell->getValue()->willReturn("Importe Euros");
        $i1Cell->getValue()->willReturn("Cierre de sesión");
        $j1Cell->getValue()->willReturn("Nº Tarjeta");
        $k1Cell->getValue()->willReturn("Titular");

        $sheet->getCell('A1')->willReturn($a1Cell);
        $sheet->getCell('B1')->willReturn($b1Cell);
        $sheet->getCell('C1')->willReturn($c1Cell);
        $sheet->getCell('D1')->willReturn($d1Cell);
        $sheet->getCell('E1')->willReturn($e1Cell);
        $sheet->getCell('F1')->willReturn($f1Cell);
        $sheet->getCell('G1')->willReturn($g1Cell);
        $sheet->getCell('H1')->willReturn($h1Cell);
        $sheet->getCell('I1')->willReturn($i1Cell);
        $sheet->getCell('J1')->willReturn($j1Cell);
        $sheet->getCell('K1')->willReturn($k1Cell);

        $sheet->getCell('A2')->willReturn($a2Cell);
        $sheet->getCell('C2')->willReturn($c2Cell);
        $sheet->getCell('D2')->willReturn($d2Cell);
        $sheet->getCell('E2')->willReturn($e2Cell);
        $sheet->getCell('F2')->willReturn($f2Cell);
        $sheet->getCell('G2')->willReturn($g2Cell);
        $sheet->getCell('H2')->willReturn($h2Cell);
        $sheet->getCell('J2')->willReturn($j2Cell);
        $sheet->getCell('K2')->willReturn($k2Cell);

        $sheet->getCell('A3')->willReturn($a3Cell);
        $sheet->getCell('C3')->willReturn($c3Cell);
        $sheet->getCell('D3')->willReturn($d3Cell);
        $sheet->getCell('E3')->willReturn($e3Cell);
        $sheet->getCell('F3')->willReturn($f3Cell);
        $sheet->getCell('G3')->willReturn($g3Cell);
        $sheet->getCell('H3')->willReturn($h3Cell);
        $sheet->getCell('J3')->willReturn($j3Cell);
        $sheet->getCell('K3')->willReturn($k3Cell);

        $sheet->getHighestColumn()->willReturn('K');
        $a4Cell->getValue()->willReturn("");
        $sheet->getCell("A4")->willReturn($a4Cell);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RedsysStatementParser::class);
    }

    function it_parses_the_file(Worksheet $sheet,
        ConsignmentFinder $consignmentFinder,
        Cell $a2Cell,
        Cell $c2Cell,
        Cell $d2Cell,
        Cell $e2Cell,
        Cell $f2Cell,
        Cell $g2Cell,
        Cell $h2Cell,
        Cell $j2Cell,
        Cell $k2Cell,
        Cell $a3Cell,
        Cell $c3Cell,
        Cell $d3Cell,
        Cell $e3Cell,
        Cell $f3Cell,
        Cell $g3Cell,
        Cell $h3Cell,
        Cell $j3Cell,
        Cell $k3Cell
    ) {
        $consignment1 = "1";
        $dateString1 = "03/01/2017";
        $date1 = \DateTime::createFromFormat('d/m/Y', $dateString1);
        $a2Cell->getValue()->willReturn($dateString1);
        $c2Cell->getValue()->willReturn("Autorización");
        $d2Cell->getValue()->willReturn("ES-11111");
        $e2Cell->getValue()->willReturn("Autorizada 123456");
        $f2Cell->getValue()->willReturn("16.00");
        $g2Cell->getValue()->willReturn("EUR");
        $h2Cell->getValue()->willReturn("16.00");
        $j2Cell->getValue()->willReturn("4589******1234");
        $k2Cell->getValue()->willReturn("Fulano");
        $consignmentFinder->findConsignment($date1, "1234", 16.00)->willReturn($consignment1);

        $consignment2 = "2";
        $dateString2 = "04/01/2017";
        $date2 = \DateTime::createFromFormat('d/m/Y', $dateString2);
        $a3Cell->getValue()->willReturn($dateString2);
        $c3Cell->getValue()->willReturn("Autorización");
        $d3Cell->getValue()->willReturn("ES-22222");
        $e3Cell->getValue()->willReturn("Autorizada 987654");
        $f3Cell->getValue()->willReturn("29.69");
        $g3Cell->getValue()->willReturn("EUR");
        $h3Cell->getValue()->willReturn("29.69");
        $j3Cell->getValue()->willReturn("4589******9876");
        $k3Cell->getValue()->willReturn("Mengano");
        $consignmentFinder->findConsignment($date2, "9876", 29.69)->willReturn($consignment2);

        $result = $this->parse($sheet, $consignmentFinder);

        $result->shouldHaveCount(2);
        $result[$consignment1][0]->getDate()->shouldBeLike($date1);
        $result[$consignment1][0]->getOrderNumber()->shouldBe("ES-11111");
        $result[$consignment1][0]->getCode()->shouldBe("123456");
        $result[$consignment1][0]->getAmount()->shouldBe(16.00);
        $result[$consignment1][0]->getOriginalAmount()->shouldBe(16.00);
        $result[$consignment1][0]->getOriginalCurrency()->shouldBe("EUR");
        $result[$consignment1][0]->getCardNumberLast()->shouldBe("1234");
        $result[$consignment1][0]->getPayerName()->shouldBe("Fulano");
        $result[$consignment2][0]->getDate()->shouldBeLike($date2);
        $result[$consignment2][0]->getOrderNumber()->shouldBe("ES-22222");
        $result[$consignment2][0]->getCode()->shouldBe("987654");
        $result[$consignment2][0]->getAmount()->shouldBe(29.69);
        $result[$consignment2][0]->getOriginalAmount()->shouldBe(29.69);
        $result[$consignment2][0]->getOriginalCurrency()->shouldBe("EUR");
        $result[$consignment2][0]->getCardNumberLast()->shouldBe("9876");
        $result[$consignment2][0]->getPayerName()->shouldBe("Mengano");
    }

    function it_parses_currencies_different_than_euros(Worksheet $sheet,
        ConsignmentFinder $consignmentFinder,
        Cell $a2Cell,
        Cell $c2Cell,
        Cell $d2Cell,
        Cell $e2Cell,
        Cell $f2Cell,
        Cell $g2Cell,
        Cell $h2Cell,
        Cell $j2Cell,
        Cell $k2Cell,
        Cell $a3Cell
    ) {
        $consignment1 = "1";
        $dateString1 = "03/01/2017";
        $date1 = \DateTime::createFromFormat('d/m/Y', $dateString1);
        $a2Cell->getValue()->willReturn($dateString1);
        $c2Cell->getValue()->willReturn("Autorización");
        $d2Cell->getValue()->willReturn("ES-11111");
        $e2Cell->getValue()->willReturn("Autorizada 123456");
        $f2Cell->getValue()->willReturn("42.00");
        $g2Cell->getValue()->willReturn("GBP");
        $h2Cell->getValue()->willReturn("47.81");
        $j2Cell->getValue()->willReturn("4589******1234");
        $k2Cell->getValue()->willReturn("Fulano");
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
        $result[$consignment1][0]->getPayerName()->shouldBe("Fulano");
    }

    function it_throws_exception_when_column_is_missing(Worksheet $sheet,
        ConsignmentFinder $consignmentFinder,
        Cell $a1Cell
    ) {

        $a1Cell->getValue()->willReturn('NotADate');

        $this->shouldThrow(new InvalidStatementException('Missing column "Fecha" in statement.'))->duringParse($sheet, $consignmentFinder);
    }
}
