<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Ui\Component\Offer\Listing;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Smile\Offer\Api\Data\OfferInterface;

/**
 * Add filter strategy for Retailer.
 */
class AddRetailerFilterToCollection implements AddFilterToCollectionInterface
{
    /**
     * @inheritdoc
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        $collection->addFieldToFilter(OfferInterface::SELLER_ID, $condition);
    }
}
