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

    function let(FinderKeyCreator $finderKeyCreator, \DateTime $date)
    {
        $date->beConstructedWith(array('2017-01-01'));
        $finderKeyCreator->createIndex($date, $this->lastDigits, $this->amount)->willReturn($this->index);
        $this->beConstructedWith(array($this->index => $this->consignment), $finderKeyCreator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConsignmentFinder::class);
    }

    function it_uses_the_index_creator(FinderKeyCreator $finderKeyCreator, \DateTime $date)
    {
        $lastDigits = '1234';
        $amount = 3434.00;
        $finderKeyCreator->createIndex($date, $lastDigits, $amount)->shouldBeCalled();

        $this->findConsignment($date, $lastDigits, $amount)->shouldReturn(null);
    }

    function it_finds_the_consignment(\DateTime $date)
    {
        $this->findConsignment($date, $this->lastDigits, $this->amount)->shouldReturn($this->consignment);
    }

    function it_returns_null_when_not_found(FinderKeyCreator $finderKeyCreator, \DateTime $date)
    {
        $finderKeyCreator->createIndex($date, '0000', $this->amount)->willReturn('another_index');

        $this->findConsignment($date, '0000', $this->amount)->shouldReturn(null);
    }
}
