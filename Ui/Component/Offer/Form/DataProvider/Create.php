<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Ui\Component\Offer\Form\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Data Provider for Retailer Offer Edit Form.
 */
class Create extends AbstractDataProvider
{
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        mixed $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return [];
    }
}
