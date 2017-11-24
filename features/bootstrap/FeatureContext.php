<?php

use Behat\Behat\Context\Context;
use Behat\Mink\Mink;
use Behat\MinkExtension\Context\MinkAwareContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;


/**
 * This context class contains the definitions of the steps used by the demo
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 *
 * @see http://behat.org/en/latest/quick_start.html
 */
class FeatureContext implements Context, MinkAwareContext
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @var Mink
     */
    private $mink;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function setMink(Mink $mink)
    {
        $this->mink = $mink;
    }

    public function setMinkParameters(array $parameters)
    {
    }

    /**
     * @Given /^the response is loadable on python$/
     */
    public function theResponseIsLoadableOnPython()
    {
        $process = new Process("python3 tests/resources/parseofx.py");
        $process
            ->setInput($this->mink->getSession()->getPage()->getContent())
            ->setTimeout(10)
            ->run()
        ;

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
