<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Model\Rule\QueryContext;

use Smile\ElasticsuiteCatalogRule\Api\Rule\QueryContext\ProviderInterface;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Provides the currently selected retailer store in drive mode for rules' query context.
 */
class RetailerStoreProvider implements ProviderInterface
{
    /**
     * Context code.
     */
    public const CODE = 'retailer_store';

    /**
     * Constructor.
     *
     * @param CurrentStore $currentStore   Current Store
     * @param Settings     $settingsHelper Setting Helper
     */
    public function __construct(
        private CurrentStore $currentStore,
        private Settings $settingsHelper
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        if ($this->settingsHelper->isDriveMode()) {
            $currentRetailer = $this->currentStore->getRetailer();
            if ($currentRetailer && $currentRetailer->getId()) {
                return $currentRetailer->getId();
            }
        }

        return '';
    }
}
