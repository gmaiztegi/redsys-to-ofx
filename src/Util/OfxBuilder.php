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
use Twig\Environment;

/**
 * Class OfxBuilder
 *
 * @author Gorka Maiztegi <gmaiztegi@gmail.com>
 */
class OfxBuilder
{
    /**
     * @var Environment
     */
    protected $templating;

    /**
     * @param Environment $environment
     */
    public function __construct(Environment $environment)
    {
        $this->templating = $environment;
    }

    /**
     * @param Transaction[] $transactions
     *
     * @return string
     */
    public function build(array $transactions, $commerceCode)
    {
        return $this->templating->render('ofx/statement.ofx.twig', [
            'commerce_code' => $commerceCode,
            'transactions' => $transactions,
        ]);
    }
}
