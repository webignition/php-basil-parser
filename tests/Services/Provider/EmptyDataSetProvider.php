<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Services\Provider;

use webignition\BasilModel\DataSet\DataSetCollectionInterface;
use webignition\BasilModelProvider\DataSet\DataSetProviderInterface;
use webignition\BasilModelProvider\Exception\UnknownDataProviderException;

class EmptyDataSetProvider implements DataSetProviderInterface
{
    /**
     * @param string $importName
     *
     * @return DataSetCollectionInterface
     *
     * @throws UnknownDataProviderException
     */
    public function findDataSetCollection(string $importName): DataSetCollectionInterface
    {
        throw new UnknownDataProviderException($importName);
    }
}
