<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Api;

use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection;

/**
 * Collection Processor : Used to filter product collection according store configuration.
 */
interface CollectionProcessorInterface
{
    /**
     * Apply store sort orders to a product collection.
     *
     * @param Collection $collection Product Collection
     */
    public function applyStoreSortOrders(Collection $collection): void;
}
