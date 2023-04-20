<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;
use Smile\RetailerOffer\Model\Config\Source\Navigation;

/**
 * Retailer Offer Helper
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Settings extends AbstractHelper
{
    /**
     * Location of Elasticsuite for Retailers base settings configuration.
     *
     * @var string
     */
    const BASE_CONFIG_XML_PREFIX = 'smile_retailersuite_retailer_base_settings';

    /**
     * @var string
     */
    const NAVIGATION_SETTINGS_CONFIG_XML_PREFIX = 'navigation_settings';

    /**
     * @var State
     */
    private State $state;

    /**
     * Settings constructor.
     *
     * @param Context $context Helper Context
     * @param State   $state   Application State
     */
    public function __construct(
        Context $context,
        State $state
    ) {
        $this->state = $state;
        parent::__construct($context);
    }

    /**
     * Check if we should enforce filtering on the current retailer for navigation in Front Office.
     *
     * @return bool
     */
    public function isDriveMode(): bool
    {
        return (bool) ($this->getCurrentMode() === Navigation::DRIVE_MODE);
    }

    /**
     * Check if we should enforce filtering on the current retailer for navigation in Front Office.
     *
     * @return bool
     */
    public function isRetailerMode(): bool
    {
        return (bool) ($this->getCurrentMode() === Navigation::RETAIL_MODE);
    }

    /**
     * Retrieve current navigation mode (drive or retail)
     *
     * @return int
     */
    public function getCurrentMode(): int
    {
        return (int) $this->getNavigationSettings('navigation_mode');
    }

    /**
     * Check if we should display other offers for products.
     *
     * @return bool
     */
    public function displayOtherOffers(): bool
    {
        return (bool) $this->scopeConfig->getValue('display_offers');
    }

    /**
     * Check if we should use store offers
     *
     * @return bool
     */
    public function useStoreOffers(): bool
    {
        return !($this->isAdmin() || !$this->isDriveMode());
    }

    /**
     * Get config value for 'display out of stock' option
     *
     * @return bool
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
     *
     * @param string $path The config path to retrieve
     *
     * @return mixed
     */
    protected function getNavigationSettings(string $path): mixed
    {
        $configPath = implode('/', [self::BASE_CONFIG_XML_PREFIX, self::NAVIGATION_SETTINGS_CONFIG_XML_PREFIX, $path]);

        return $this->scopeConfig->getValue($configPath);
    }

    /**
     * Check if we are browsing admin area
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isAdmin(): bool
    {
        return $this->state->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
    }
}
