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
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\Offer\Api\OfferManagementInterface;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Generic Helper for Retailer Offer
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Offer extends AbstractHelper
{
    /**
     * @var OfferManagementInterface
     */
    private OfferManagementInterface $offerManagement;

    /**
     * @var CurrentStore
     */
    private CurrentStore $currentStore;

    /**
     * @var OfferInterface[]
     */
    private array $offersCache = [];

    /**
     * ProductPlugin constructor.
     *
     * @param Context                   $context         Helper context.
     * @param OfferManagementInterface  $offerManagement The offer Management
     * @param CurrentStore              $currentStore    Current Store Provider
     */
    public function __construct(
        Context $context,
        OfferManagementInterface $offerManagement,
        CurrentStore $currentStore
    ) {
        $this->offerManagement = $offerManagement;
        $this->currentStore    = $currentStore;

        parent::__construct($context);
    }

    /**
     * Retrieve Offer for the product by retailer id.
     *
     * @param ProductInterface $product    The product
     * @param int              $retailerId The retailer Id
     *
     * @return OfferInterface
     */
    public function getOffer(ProductInterface $product, int $retailerId): OfferInterface
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
     * @return ?OfferInterface
     */
    public function getCurrentOffer(ProductInterface $product): ?OfferInterface
    {
        $offer = null;

        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            $offer = $this->getOffer($product, $this->currentStore->getRetailer()->getId());
        }

        return $offer;
    }
}
