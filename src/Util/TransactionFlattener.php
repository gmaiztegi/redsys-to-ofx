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

/**
 * Class TransactionFlattener
 *
 * @author Gorka Maiztegi <gmaiztegi@gmail.com>
 */
class TransactionFlattener
{
    /**
     * @param Transaction[][] $consignments
     *
     * @return Transaction[]
     */
    public function flatten(array $consignments)
    {
        $flattenedTransactions = array();

        foreach ($consignments as $consignmentNumber => $transactions) {
            $flattenedTransactions = array_merge($flattenedTransactions, $transactions);

            $totalAmount = $this->totalAmount($transactions);
            $date = clone $transactions[0]->getDate();
            $date->add(\DateInterval::createFromDateString('1 day'));

            $consignment = new Transaction();
            $consignment->setCode($consignmentNumber);
            $consignment->setOrderNumber('Consignment');
            $consignment->setAmount(-$totalAmount);
            $consignment->setDate($date);

            $flattenedTransactions[] = $consignment;
        }

        return $flattenedTransactions;
    }

    /**
     * @param Transaction[] $transactions
     *
     * @return float
     */
    private function totalAmount(array $transactions)
    {
        $amount = 0.0;

        foreach ($transactions as $transaction) {
            $amount += $transaction->getAmount();
        }

        return $amount;
    }
}
