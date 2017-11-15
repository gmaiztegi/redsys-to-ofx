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

/**
 * Class OrderNumberFinder
 *
 * @author Gorka Maiztegi <gmaiztegi@gmail.com>
 */
class ConsignmentFinder
{
    protected $table;

    /**
     * OrderNumberFinder constructor.
     * @param array $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * @param \DateTime $date
     * @param string    $cardNumberLast
     * @param float     $amount
     *
     * @return int
     */
    public function findConsignment(\DateTime $date, $cardNumberLast, $amount)
    {
        $key = $date->format('Ymd').$cardNumberLast.sprintf('%.2f', $amount);

        if (!isset($this->table[$key])) {
            $datePlusOne = clone $date;
            $datePlusOne->add(\DateInterval::createFromDateString('1 day'));
            $key = $date->format('Ymd').$cardNumberLast.sprintf('%.2f', $amount);
        }

        return isset($this->table[$key]) ? $this->table[$key] : null;
    }
}
