<?php

namespace webignition\BasilParser\Model\PageUrlReference;

class PageUrlReference implements PageUrlReferenceInterface
{
    const PART_DELIMITER = '.';
    const EXPECTED_PART_COUNT = 2;

    const IMPORT_NAME_INDEX = 0;
    const URL_PART_INDEX = 1;
    const EXPECTED_URL_PART = 'url';

    private $importName = '';
    private $isValid = false;
    private $reference  = '';

    public function __construct(string $reference)
    {
        $reference = trim($reference);
        $this->reference = $reference;

        $referenceParts = explode(self::PART_DELIMITER, $reference);

        $hasExpectedPartCount = self::EXPECTED_PART_COUNT === count($referenceParts);

        if ($hasExpectedPartCount && self::EXPECTED_URL_PART === $referenceParts[self::URL_PART_INDEX]) {
            $this->importName = $referenceParts[self::IMPORT_NAME_INDEX];
            $this->isValid = true;
        }
    }

    public function getImportName(): string
    {
        return $this->importName;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function __toString(): string
    {
        return $this->reference;
    }
}
