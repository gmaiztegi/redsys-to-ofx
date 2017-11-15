<?php

/*
 * This file is part of the Redsys to OFX package.
 *
 * (c) Gorka Maiztegi <gmaiztegi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RedsysStatement
 *
 * @author Gorka Maiztegi <gmaiztegi@gmail.com>
 */
class RedsysStatement
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $commerceId;

    /**
     * @var UploadedFile
     *
     * @Assert\NotBlank()
     * @Assert\File(mimeTypes={ "application/vnd.ms-excel", "application/CDFV2-unknown" })
     */
    protected $consignmentStatement;

    /**
     * @var UploadedFile
     *
     * @Assert\NotBlank()
     * @Assert\File(mimeTypes={ "text/csv", "text/plain" })
     */
    protected $transactionStatement;

    /**
     * @return int
     */
    public function getCommerceId()
    {
        return $this->commerceId;
    }

    /**
     * @param int $commerceId
     */
    public function setCommerceId($commerceId)
    {
        $this->commerceId = $commerceId;
    }

    /**
     * @return UploadedFile
     */
    public function getConsignmentStatement()
    {
        return $this->consignmentStatement;
    }

    /**
     * @param UploadedFile $consignmentStatement
     */
    public function setConsignmentStatement($consignmentStatement)
    {
        $this->consignmentStatement = $consignmentStatement;
    }

    /**
     * @return UploadedFile
     */
    public function getTransactionStatement()
    {
        return $this->transactionStatement;
    }

    /**
     * @param UploadedFile $transactionStatement
     */
    public function setTransactionStatement($transactionStatement)
    {
        $this->transactionStatement = $transactionStatement;
    }
}
