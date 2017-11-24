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

use App\Util\OfxBuilder;
use PhpSpec\ObjectBehavior;
use Twig\Environment;

class OfxBuilderSpec extends ObjectBehavior
{
    function let(Environment $twigEnvironment)
    {
        $this->beConstructedWith($twigEnvironment);
    }

    function it_is_initializable(Environment $twigEnvironment)
    {
        $this->shouldHaveType(OfxBuilder::class);
    }

    function it_renders_the_ofx_file(Environment $twigEnvironment)
    {
        $transactions = array();
        $commerceCode = 'thecode';
        $result = 'the_result';

        $twigEnvironment->render('ofx/statement.ofx.twig', array(
            'commerce_code' => $commerceCode,
            'transactions' => $transactions,
        ))->shouldBeCalled()->willReturn($result);

        $this->build($transactions, $commerceCode)->shouldReturn($result);
    }
}
