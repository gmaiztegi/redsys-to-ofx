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
use PhpSpec\ObjectBehavior;

class ConsignmentFinderSpec extends ObjectBehavior
{
    private $lastDigits = '1234';
    private $amount = 10.00;
    private $index = 'the_index';
    private $consignment = 'the_consignment';
    private $date;
    private $datePlusOne;

    function let(FinderKeyCreator $finderKeyCreator)
    {
        $this->date = new \DateTime('2017-01-01');
        $this->datePlusOne = new \DateTime('2017-01-02');
        $finderKeyCreator->createIndex($this->date, $this->lastDigits, $this->amount)->willReturn($this->index);
        $finderKeyCreator->createIndex($this->datePlusOne, $this->lastDigits, $this->amount)->willReturn($this->index);
        $this->beConstructedWith(array($this->index => $this->consignment), $finderKeyCreator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConsignmentFinder::class);
    }

    function it_uses_the_index_creator(FinderKeyCreator $finderKeyCreator)
    {
        $lastDigits = '1234';
        $amount = 3434.00;
        $finderKeyCreator->createIndex($this->date, $lastDigits, $amount)->shouldBeCalled();
        $finderKeyCreator->createIndex($this->datePlusOne, $lastDigits, $amount)->shouldBeCalled();

        $this->findConsignment($this->date, $lastDigits, $amount)->shouldReturn(null);
    }

    function it_finds_the_consignment()
    {
        $this->findConsignment($this->date, $this->lastDigits, $this->amount)->shouldReturn($this->consignment);
    }

    function it_returns_null_when_not_found(FinderKeyCreator $finderKeyCreator)
    {
        $finderKeyCreator->createIndex($this->date, '0000', $this->amount)->willReturn('another_index');
        $finderKeyCreator->createIndex($this->datePlusOne, '0000', $this->amount)->willReturn('another_index');

        $this->findConsignment($this->date, '0000', $this->amount)->shouldReturn(null);
    }
}
