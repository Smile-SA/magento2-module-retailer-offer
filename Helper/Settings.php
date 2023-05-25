<?php

namespace Smile\RetailerOffer\Helper;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Smile\RetailerOffer\Model\Config\Source\Navigation;

/**
 * Retailer Offer Helper.
 */
class Settings extends AbstractHelper
{
    private const BASE_CONFIG_XML_PREFIX = 'smile_retailersuite_retailer_base_settings';
    private const NAVIGATION_SETTINGS_CONFIG_XML_PREFIX = 'navigation_settings';

    public function __construct(
        Context $context,
        private State $state
    ) {
        parent::__construct($context);
    }

    /**
     * Check if we should enforce filtering on the current retailer for navigation in Front Office.
     */
    public function isDriveMode(): bool
    {
        return $this->getCurrentMode() === Navigation::DRIVE_MODE;
    }

    /**
     * Check if we should enforce filtering on the current retailer for navigation in Front Office.
     */
    public function isRetailerMode(): bool
    {
        return $this->getCurrentMode() === Navigation::RETAIL_MODE;
    }

    /**
     * Retrieve current navigation mode (drive or retail)
     */
    public function getCurrentMode(): int
    {
        return (int) $this->getNavigationSettings('navigation_mode');
    }

    /**
     * Check if we should display other offers for products.
     */
    public function displayOtherOffers(): bool
    {
        return (bool) $this->scopeConfig->getValue('display_offers');
    }

    /**
     * Check if we should use store offers
     */
    public function useStoreOffers(): bool
    {
        return !($this->isAdmin() || !$this->isDriveMode());
    }

    /**
     * Get config value for 'display out of stock' option
     */
    public function isEnabledShowOutOfStock(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve Retailer Configuration for a given field.
     */
    protected function getNavigationSettings(string $path): mixed
    {
        $configPath = implode('/', [self::BASE_CONFIG_XML_PREFIX, self::NAVIGATION_SETTINGS_CONFIG_XML_PREFIX, $path]);

        return $this->scopeConfig->getValue($configPath);
    }

    /**
     * Check if we are browsing admin area.
     *
     * @throws LocalizedException
     */
    private function isAdmin(): bool
    {
        return $this->state->getAreaCode() == FrontNameResolver::AREA_CODE;
    }
}
