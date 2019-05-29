<?php

namespace webignition\BasilParser\Provider\Step;

use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\Loader\StepLoader;
use webignition\BasilParser\Model\Step\StepInterface;

class DeferredStepProvider implements StepProviderInterface
{
    private $stepLoader;
    private $importPaths;
    private $steps = [];

    public function __construct(StepLoader $stepLoader, array $importPaths)
    {
        $this->stepLoader = $stepLoader;
        $this->importPaths = $importPaths;
    }

    /**
     * @param string $importName
     *
     * @return StepInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     */
    public function findStep(string $importName): StepInterface
    {
        $step = $this->steps[$importName] ?? null;

        if (null === $step) {
            $step = $this->retrieveStep($importName);
            $this->steps[$importName] = $step;
        }

        return $step;
    }

    /**
     * @param string $importName
     *
     * @return StepInterface
     *
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws UnknownPageException
     * @throws UnknownStepException
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageElementException
     */
    private function retrieveStep(string $importName): StepInterface
    {
        $importPath = $this->importPaths[$importName] ?? null;

        if (null === $importPath) {
            throw new UnknownStepException($importName);
        }

        try {
            return $this->stepLoader->load($importPath);
        } catch (YamlLoaderException $yamlLoaderException) {
            throw new NonRetrievableStepException($importName, $importPath, $yamlLoaderException);
        }
    }
}