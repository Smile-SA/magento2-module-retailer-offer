<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Ui\Component\Offer\Listing;

use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Magento\Framework\Data\Collection;
use Smile\Offer\Api\Data\OfferInterface;

/**
 * Add filter strategy for Retailer
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class AddRetailerFilterToCollection implements AddFilterToCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function addFilter(Collection $collection, $field, $condition = null): void
    {
        $collection->addFieldToFilter(OfferInterface::SELLER_ID, $condition);
    }
}
