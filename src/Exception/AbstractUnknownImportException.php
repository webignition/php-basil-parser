<?php

namespace webignition\BasilParser\Exception;

use webignition\BasilParser\Model\ExceptionContext\ExceptionContext;

abstract class AbstractUnknownImportException extends \Exception implements ContextAwareExceptionInterface
{
    use ContextAwareExceptionTrait;

    private $importName;

    public function __construct(string $importName, string $message)
    {
        parent::__construct($message);

        $this->importName = $importName;
        $this->exceptionContext = new ExceptionContext();
    }

    public function getImportName(): string
    {
        return $this->importName;
    }
}
