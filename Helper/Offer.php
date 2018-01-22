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
namespace Smile\RetailerOffer\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Generic Helper for Retailer Offer
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Offer extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Smile\Offer\Api\OfferManagementInterface
     */
    private $offerManagement;

    /**
     * @var \Smile\StoreLocator\CustomerData\CurrentStore
     */
    private $currentStore;

    /**
     * @var OfferInterface[]
     */
    private $offersCache = [];

    /**
     * ProductPlugin constructor.
     *
     * @param \Magento\Framework\App\Helper\Context         $context         Helper context.
     * @param \Smile\Offer\Api\OfferManagementInterface     $offerManagement The offer Management
     * @param \Smile\StoreLocator\CustomerData\CurrentStore $currentStore    Current Store Provider
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Smile\Offer\Api\OfferManagementInterface $offerManagement,
        \Smile\StoreLocator\CustomerData\CurrentStore $currentStore
    ) {
        $this->offerManagement = $offerManagement;
        $this->currentStore    = $currentStore;

        parent::__construct($context);
    }

    /**
     * Retrieve Offer for the product by retailer id.
     *
     * @param ProductInterface $product    The product
     * @param integer          $retailerId The retailer Id
     *
     * @return \Smile\Offer\Api\Data\OfferInterface
     */
    public function getOffer($product, $retailerId)
    {
        $offer = null;

        if ($product->getId() && $retailerId) {
            $cacheKey = implode('_', [$product->getId(), $retailerId]);

            if (false === isset($this->offersCache[$cacheKey])) {
                $offer                        = $this->offerManagement->getOffer($product->getId(), $retailerId);
                $this->offersCache[$cacheKey] = $offer;
            }

            $offer = $this->offersCache[$cacheKey];
        }

        return $offer;
    }

    /**
     * Retrieve Current Offer for the product.
     *
     * @param ProductInterface $product The product
     *
     * @return \Smile\Offer\Api\Data\OfferInterface
     */
    public function getCurrentOffer($product)
    {
        $offer = null;

        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            $offer = $this->getOffer($product, $this->currentStore->getRetailer()->getId());
        }

        return $offer;
    }
}
