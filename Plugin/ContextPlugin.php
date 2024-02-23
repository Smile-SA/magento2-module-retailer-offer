<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Plugin;

use Closure;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Plugin to ensure the context properly vary according to currently selected (or not) retailer.
 */
class ContextPlugin
{
    public function __construct(
        private Context $httpContext,
        private CurrentStore $currentStore,
        private Settings $settingsHelper
    ) {
    }

    /**
     * Ensure proper vary on frontend according to current Retailer Id (if any).
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        ActionInterface $subject,
        Closure $proceed,
        RequestInterface $request
    ): mixed {

        // show product offer price if shop has been selected, even in Retail mode
        if ($this->settingsHelper->isDriveMode() || $this->currentStore->getRetailer()) {
            // Set a default value to have common vary for all customers without any chosen retailer.
            $retailerId = 'default';

            if ($this->currentStore->getRetailer()
                && $this->currentStore->getRetailer()->getId()) {
                $retailerId = $this->currentStore->getRetailer()->getId();
            }

            $this->httpContext->setValue(
                CurrentStore::CONTEXT_RETAILER,
                $retailerId,
                false
            );
        }

        return $proceed($request);
    }
}
