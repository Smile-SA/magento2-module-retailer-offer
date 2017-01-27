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

use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Plugin to proceed Quote update when changing current Retailer or Pickup Date.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalog
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class CurrentStorePlugin
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
     * Proceed current Quote update when changing current Retailer.
     *
     * @param \Smile\StoreLocator\CustomerData\CurrentStore $currentStore The Retailer Data object
     * @param \Closure                                      $proceed      The setParams method of retailer data object
     * @param \Smile\Retailer\Api\Data\RetailerInterface    $retailer     The Retailer
     *
     * @return \Smile\StoreLocator\CustomerData\CurrentStore
     */
    public function aroundSetRetailer(CurrentStore $currentStore, \Closure $proceed, $retailer)
    {
        $quote      = $this->checkoutSession->getQuote();
        $hasChanges = (
            ($currentStore->getRetailer() && ($currentStore->getRetailer()->getId() !== $retailer->getId()))
            || ($quote->getSellerId() !== $retailer->getId())
        );

        $proceed($retailer);

        if ($hasChanges) {
            $quote = $this->checkoutSession->getQuote();
            $quote->setSellerId($retailer->getId())
                  ->setTotalsCollectedFlag(false)
                  ->collectTotals()
                  ->save();
            $this->checkoutSession->setQuoteId($quote->getId());
        }

        return $currentStore;
    }
}
