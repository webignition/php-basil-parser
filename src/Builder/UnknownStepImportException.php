<?php

namespace webignition\BasilParser\Builder;

class UnknownStepImportException extends \Exception
{
    private $stepName;
    private $importName;
    private $stepImportPaths;

    public function __construct(string $stepName, string $importName, array $stepImportPaths)
    {
        parent::__construct(
            'Unknown import "' . $importName . '" in step "' . $importName . '"'
        );

        $this->stepName = $stepName;
        $this->importName = $importName;
        $this->stepImportPaths = $stepImportPaths;
    }
}