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
 * Class FinderIndexCreator
 *
 * @author Gorka Maiztegi <gmaiztegi@gmail.com>
 */
class FinderKeyCreator
{
    /**
     * @param \DateTime $date
     * @param string    $cardNumberLast
     * @param float     $amount
     *
     * @return string
     */
    public function createIndex(\DateTime $date, $cardNumberLast, $amount)
    {
        return $date->format('Ymd').$cardNumberLast.sprintf('%.2f', $amount);
    }
}
