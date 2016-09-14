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
 * Plugin to proceed Quote update when changing current Retailer or Pickup Date.
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

    /**
     * RetailerDataPlugin constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession The Checkout Session
     */
    public function __construct(\Magento\Checkout\Model\Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Proceed current Quote update when changing current Retailer or Pickup Date.
     *
     * @param \Smile\Retailer\CustomerData\RetailerData $retailerData The Retailer Data object
     * @param \Closure                                  $proceed      The setParams method of retailer data object
     * @param integer                                   $retailerId   The Retailer Id
     * @param string                                    $pickupDate   The Pickup Date
     *
     * @return \Smile\Retailer\CustomerData\RetailerData
     */
    public function aroundSetParams(RetailerData $retailerData, \Closure $proceed, $retailerId, $pickupDate)
    {
        $quote      = $this->checkoutSession->getQuote();
        $hasChanges = (
            ($retailerData->getRetailerId() !== $retailerId || $retailerData->getPickupDate() !== $pickupDate)
            || ($quote->getSellerId() !== $retailerId || $quote->getPickupDate() !== $pickupDate)
        );

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
