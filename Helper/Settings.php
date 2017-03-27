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
     * Location of RetailerSuite base settings configuration.
     *
     * @var string
     */
    const BASE_CONFIG_XML_PREFIX = 'smile_retailersuite_retailer_base_settings';

    /**
     * @var string
     */
    const NAVIGATION_SETTINGS_CONFIG_XML_PREFIX = 'navigation_settings';

    /**
     * Check if we should enforce filtering on the current retailer for navigation in Front Office.
     *
     * @return bool
     */
    public function isDriveMode()
    {
        return (bool) ($this->getCurrentMode() === Navigation::DRIVE_MODE);
    }

    /**
     * Check if we should enforce filtering on the current retailer for navigation in Front Office.
     *
     * @return bool
     */
    public function isRetailerMode()
    {
        return (bool) ($this->getCurrentMode() === Navigation::RETAIL_MODE);
    }

    /**
     * Retrieve current navigation mode (drive or retail)
     *
     * @return string
     */
    public function getCurrentMode()
    {
        return (string) $this->getNavigationSettings('navigation_mode');
    }

    /**
     * Check if we should display other offers for products.
     *
     * @return bool
     */
    public function displayOtherOffers()
    {
        return (bool) $this->scopeConfig->getValue('display_offers');
    }

    /**
     * Retrieve Retailer Configuration for a given field.
     *
     * @param string $path The config path to retrieve
     *
     * @return mixed
     */
    protected function getNavigationSettings($path)
    {
        $configPath = implode('/', [self::BASE_CONFIG_XML_PREFIX, self::NAVIGATION_SETTINGS_CONFIG_XML_PREFIX, $path]);

        return $this->scopeConfig->getValue($configPath);
    }
}
