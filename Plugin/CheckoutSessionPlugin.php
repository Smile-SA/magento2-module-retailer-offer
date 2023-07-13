<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Plugin;

use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Ensure correct appliance of retailer data to quote when retrieving it.
 * We may have a quote with no values if retailer data are properly stored
 * in cookies but not re-applied when switching retailer.
 */
class CheckoutSessionPlugin
{
    public function __construct(private CurrentStore $currentStore)
    {
    }

    /**
     * Ensure proper binding of seller id and pickup date when retrieving quote from session.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetQuote(Session $session, Quote $result): Quote
    {
        if (!$result->getSellerId() && $this->currentStore->getRetailer()) {
            $result->setSellerId($this->currentStore->getRetailer()->getId());
        }

        return $result;
    }
}
