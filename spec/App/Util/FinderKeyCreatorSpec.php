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
use PhpSpec\ObjectBehavior;

class FinderKeyCreatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FinderKeyCreator::class);
    }

    function it_creates_correct_string(\DateTime $date)
    {
        $date = new \DateTime('2017-01-22 16:45');
        $this->createIndex($date, '1234', 10.00)->shouldReturn('20170122123410.00');
    }

    function it_prints_date_without_hour(\DateTime $date)
    {
        $date->format('Ymd')->shouldBeCalled();
        $this->createIndex($date, '1234', 10.00);
    }
}
