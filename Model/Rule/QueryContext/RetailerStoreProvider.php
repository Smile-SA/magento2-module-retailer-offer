<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2026 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\RetailerOffer\Model\Rule\QueryContext;

use Smile\ElasticsuiteCatalogRule\Api\Rule\QueryContext\ProviderInterface;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Provides the currently selected retailer store in drive mode for rules' query context.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogRule
 * @author   Richard Bayet <richard.bayet@smile.fr>
 */
class RetailerStoreProvider implements ProviderInterface
{
    /**
     * Context code.
     */
    const CODE = 'retailer_store';

    /**
     * @var CurrentStore
     */
    private $currentStore;

    /**
     * @var Settings
     */
    private $settingsHelper;

    /**
     * Constructor.
     *
     * @param CurrentStore $currentStore   Current Store
     * @param Settings     $settingsHelper Setting Helper
     */
    public function __construct(
        CurrentStore $currentStore,
        Settings $settingsHelper
    ) {
        $this->currentStore = $currentStore;
        $this->settingsHelper = $settingsHelper;
    }

    /**
     * {@inheritdoc}
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
