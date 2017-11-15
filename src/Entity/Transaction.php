<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Gorka Maiztegi <gmaiztegi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

/**
 * Class Transaction
 *
 * @author Gorka Maiztegi <gmaiztegi@gmail.com>
 */
class Transaction
{
    /**
     * @var int
     */
    protected $code;

    /**
     * @var string
     */
    protected $orderNumber;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var int
     */
    protected $cardNumberLast;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getCardNumberLast()
    {
        return $this->cardNumberLast;
    }

    /**
     * @param int $cardNumberLast
     */
    public function setCardNumberLast($cardNumberLast)
    {
        $this->cardNumberLast = $cardNumberLast;
    }
}
