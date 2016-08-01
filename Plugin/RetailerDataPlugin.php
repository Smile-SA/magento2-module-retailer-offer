<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalog
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin;

use Smile\Retailer\CustomerData\RetailerData;

/**
 * Replace is in stock native filter on layer.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalog
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class RetailerDataPlugin
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    public function __construct(\Magento\Checkout\Model\Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    public function aroundSetParams(RetailerData $retailerData, \Closure $proceed, $retailerId, $pickupDate)
    {
        $hasChanges = $retailerData->getRetailerId() !== $retailerId || $retailerData->getPickupDate() !== $pickupDate;

        $proceed($retailerId, $pickupDate);

        if ($hasChanges) {
            $quote = $this->checkoutSession->getQuote();
            $quote->setSellerId($retailerId)
                  ->setPickupDate($pickupDate)
                  ->setTotalsCollectedFlag(false)
                  ->collectTotals()
                  ->save();
            $this->checkoutSession->setQuoteId($quote->getId());
        }

        return $retailerData;
    }
}
