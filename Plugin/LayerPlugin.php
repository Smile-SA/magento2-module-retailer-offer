<?php

namespace Smile\RetailerOffer\Plugin;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Smile\RetailerOffer\Api\CollectionProcessorInterface;

/**
 * Add filtering for the current offer to the catalog.
 */
class LayerPlugin
{
    public function __construct(private CollectionProcessorInterface $collectionProcessor)
    {
    }

    /**
     * Add offer filtering.
     */
    public function beforePrepareProductCollection(Layer $layer, AbstractCollection $collection): void
    {
        $this->collectionProcessor->applyStoreSortOrders($collection);
    }
}
