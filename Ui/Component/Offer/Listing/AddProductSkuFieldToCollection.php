<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Ui\Component\Offer\Listing;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Smile\Offer\Model\ResourceModel\Offer\Grid\Collection as OfferGridCollection;

/**
 * Add field strategy for Product SKU Field.
 */
class AddProductSkuFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * @inheritdoc
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        /** @var OfferGridCollection $collection */
        $collection->addProductSkuToSelect();
    }
}
