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
    /**
     * @var array
     */
    protected $table;

    /**
     * @var FinderKeyCreator
     */
    protected $finderKeyCreator;

    /**
     * OrderNumberFinder constructor.
     * @param array            $table
     * @param FinderKeyCreator $finderKeyCreator
     */
    public function __construct($table, FinderKeyCreator $finderKeyCreator)
    {
        $this->table = $table;
        $this->finderKeyCreator = $finderKeyCreator;
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
        $key = $this->finderKeyCreator->createIndex($date, $cardNumberLast, $amount);

        if (!isset($this->table[$key])) {
            $datePlusOne = clone $date;
            $datePlusOne->add(\DateInterval::createFromDateString('1 day'));
            $key = $this->finderKeyCreator->createIndex($datePlusOne, $cardNumberLast, $amount);
        }

        return isset($this->table[$key]) ? $this->table[$key] : null;
    }
}
