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

use Magento\Checkout\Model\Session;
use Smile\Retailer\Api\Data\RetailerInterface;
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
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * RetailerDataPlugin constructor.
     *
     * @param Session $checkoutSession The Checkout Session
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Proceed current Quote update when changing current Retailer.
     *
     * @param CurrentStore      $currentStore The Retailer Data object
     * @param \Closure          $proceed      The setParams method of retailer data object
     * @param RetailerInterface $retailer     The Retailer
     *
     * @return CurrentStore
     */
    public function aroundSetRetailer(
        CurrentStore $currentStore,
        \Closure $proceed,
        RetailerInterface $retailer
    ): CurrentStore {
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
