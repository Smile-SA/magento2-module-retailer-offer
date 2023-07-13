<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Ui\Component\Offer\Listing;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Smile\Offer\Model\ResourceModel\Offer\Grid\Collection as OfferGridCollection;

/**
 * Add filter strategy for Product SKU Field.
 */
class AddProductSkuFilterToCollection implements AddFilterToCollectionInterface
{
    /**
     * @inheritdoc
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        /** @var OfferGridCollection $collection */
        $collection->setSkuFilter($condition);
    }
}
