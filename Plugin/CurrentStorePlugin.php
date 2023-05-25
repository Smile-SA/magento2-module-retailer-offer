<?php

namespace Smile\RetailerOffer\Plugin;

use Closure;
use Magento\Checkout\Model\Session;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Plugin to proceed Quote update when changing current Retailer or Pickup Date
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CurrentStorePlugin
{
    public function __construct(private Session $checkoutSession)
    {
    }

    /**
     * Proceed current Quote update when changing current Retailer.
     */
    public function aroundSetRetailer(
        CurrentStore $currentStore,
        Closure $proceed,
        RetailerInterface $retailer
    ): CurrentStore {
        $quote = $this->checkoutSession->getQuote();
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
