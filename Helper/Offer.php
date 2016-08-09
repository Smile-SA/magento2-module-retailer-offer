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
use Smile\Retailer\CustomerData\RetailerData;

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
    private $offerManagement;

    /**
     * @var OfferInterface[]
     */
    private $offersCache = [];

    /**
     * ProductPlugin constructor.
     * @param Context                  $context         Helper context.
     * @param OfferManagementInterface $offerManagement The offer Management
     */
    public function __construct(Context $context, OfferManagementInterface $offerManagement)
    {
        $this->offerManagement = $offerManagement;

        parent::__construct($context);
    }

    /**
     * Retrieve Offer for the product by retailer id and pickup date.
     *
     * @param ProductInterface $product    The product
     * @param integer          $retailerId The retailer Id
     * @param string           $pickupDate The pickup Date
     *
     * @return OfferInterface
     */
    public function getOffer($product, $retailerId, $pickupDate)
    {
        $offer = null;

        if ($product->getId() && $retailerId && $pickupDate) {
            $cacheKey = implode('_', [$product->getId(), $retailerId, $pickupDate]);

            if (false === isset($this->offersCache[$cacheKey])) {
                $offer = $this->offerManagement->getOffer($product->getId(), $retailerId, $pickupDate);
                $this->offersCache[$cacheKey] = $offer;
            }

            $offer = $this->offersCache[$cacheKey];
        }

        return $offer;
    }
}
