<?php

namespace webignition\BasilParser\Tests\Services\Provider;

use webignition\BasilModel\Step\StepInterface;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Provider\Step\StepProviderInterface;

class EmptyStepProvider implements StepProviderInterface
{
    /**
     * @param string $importName
     *
     * @return StepInterface
     *
     * @throws UnknownStepException
     */
    public function findStep(string $importName): StepInterface
    {
        throw new UnknownStepException($importName);
    }
}