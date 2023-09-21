<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Plugin;

use Magento\Catalog\Model\Layer;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection as ProductFulltextCollection;
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
    public function beforePrepareProductCollection(Layer $layer, ProductFulltextCollection $collection): void
    {
        $this->collectionProcessor->applyStoreSortOrders($collection);
    }
}
