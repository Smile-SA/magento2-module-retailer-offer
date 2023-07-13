<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Ui\Component\Offer\Listing;

use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Smile\Offer\Model\ResourceModel\Offer\Grid\CollectionFactory;

/**
 * Data Provider for UI Retailer Offer.
 */
class DataProvider extends AbstractDataProvider
{
    protected CollectionFactory $collectionFactory;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        private array $addFieldStrategies = [],
        private array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->collection->addFilterToMap('offer_id', 'main_table.offer_id');
    }

    /**
     * @inheritdoc
     */
    public function addField($field, $alias = null): void
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        }

        if (!isset($this->addFieldStrategies[$field])) {
            parent::addField($field, $alias);
        }
    }

    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter): mixed
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        }
        if (!isset($this->addFilterStrategies[$filter->getField()])) {
            parent::addFilter($filter);
        }
    }
}
