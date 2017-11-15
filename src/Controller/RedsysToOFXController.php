<?php

/*
 * This file is part of the Redsys to OFX package.
 *
 * (c) Gorka Maiztegi <gmaiztegi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\RedsysStatement;
use App\Exception\InvalidStatementException;
use App\Form\RedsysStatementType;
use App\Util\OfxBuilder;
use App\Util\TransactionParser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class RedsysToOFXController
 *
 * @author Gorka Maiztegi <gmaiztegi@gmail.com>
 */
class RedsysToOFXController extends Controller
{
    /**
     * @Route("/")
     *
     * @Template("index.html.twig")
     *
     * @param Request $request
     *
     * @return Response|array
     */
    public function indexAction(Request $request)
    {
        $redsysStatement = new RedsysStatement();
        $form = $this->createForm(RedsysStatementType::class, $redsysStatement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transactionParser = $this->get(TransactionParser::class);

            try {
                $transactions = $transactionParser->parseTransactionList($redsysStatement->getConsignmentStatement(), $redsysStatement->getTransactionStatement());

                $ofxBuilder = $this->get(OfxBuilder::class);
                $ofxFile = $ofxBuilder->build($transactions, $redsysStatement->getCommerceId());

                $response = new Response($ofxFile);

                $disposition = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    'redsys.ofx'
                );

                $response->headers->set('Content-Disposition', $disposition);

                return $response;
            } catch (InvalidStatementException $ex) {
                return [
                    'form' => $form->createView(),
                    'error' => $ex->getMessage(),
                ];
            }
        }

        return [
            'form' => $form->createView(),
            'error' => null,
        ];
    }
}
