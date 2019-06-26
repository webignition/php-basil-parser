<?php

namespace webignition\BasilParser\DataStructure;

class Step extends AbstractDataStructure
{
    const KEY_ACTIONS = 'actions';
    const KEY_ASSERTIONS = 'assertions';
    const KEY_USE = 'use';
    const KEY_DATA = 'data';
    const KEY_ELEMENTS = 'elements';

    public function getActionStrings(): array
    {
        return $this->getArray(self::KEY_ACTIONS);
    }

    public function getAssertionStrings(): array
    {
        return $this->getArray(self::KEY_ASSERTIONS);
    }

    public function getImportName(): string
    {
        return $this->getString(self::KEY_USE);
    }

    public function getDataArray(): array
    {
        return $this->getArray(self::KEY_DATA);
    }

    public function getDataImportName(): string
    {
        return $this->getString(self::KEY_DATA);
    }

    public function getElementStrings(): array
    {
        return $this->getArray(self::KEY_ELEMENTS);
    }
}
