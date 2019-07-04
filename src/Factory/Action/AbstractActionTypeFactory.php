<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilModel\Action\ActionInterface;

abstract class AbstractActionTypeFactory implements ActionTypeFactoryInterface
{
    public function handles(string $type): bool
    {
        return in_array($type, $this->getHandledActionTypes());
    }

    /**
     * @return string[]
     */
    abstract protected function getHandledActionTypes(): array;

    /**
     * @param string $type
     * @param string $arguments
     *
     * @return ActionInterface
     */
    abstract protected function doCreateForActionType(string $type, string $arguments): ActionInterface;

    public function createForActionType(
        string $type,
        string $arguments
    ): ActionInterface {
        if (!$this->handles($type)) {
            throw new \RuntimeException('Invalid action type');
        }

        return $this->doCreateForActionType($type, $arguments);
    }
}
