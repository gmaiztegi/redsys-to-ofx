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

use App\Entity\Transaction;
use App\Util\TransactionFlattener;
use PhpSpec\ObjectBehavior;

class TransactionFlattenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TransactionFlattener::class);
    }

    function it_creates_the_consignments()
    {
        $transaction1 = new Transaction();
        $transaction1->setDate(new \DateTime('2017-01-01'));
        $transaction1->setAmount(10.00);
        $transaction2 = new Transaction();
        $transaction2->setDate(new \DateTime('2017-01-01'));
        $transaction2->setAmount(10.00);
        $transaction3 = new Transaction();
        $transaction3->setDate(new \DateTime('2017-01-02'));
        $transaction3->setAmount(10.00);

        $consignment1 = 'the_consignment1';
        $consignment2 = 'the_consignment2';
        $transactions = array(
            $consignment1 => array($transaction1, $transaction2),
            $consignment2 => array($transaction3),
        );

        $result = $this->flatten($transactions);

        $result->shouldHaveCount(5);
        $result[0]->shouldBe($transaction1);
        $result[1]->shouldBe($transaction2);
        $result[3]->shouldBe($transaction3);
        $result[2]->getAmount()->shouldEqual(-($transaction1->getAmount()+$transaction2->getAmount()));
        $result[2]->getCode()->shouldEqual($consignment1);
        $result[2]->getDate()->shouldBeLike(new \DateTime('2017-01-02'));
        $result[4]->getAmount()->shouldEqual(-$transaction3->getAmount());
        $result[4]->getCode()->shouldEqual($consignment2);
        $result[4]->getDate()->shouldBeLike(new \DateTime('2017-01-03'));
    }
}
