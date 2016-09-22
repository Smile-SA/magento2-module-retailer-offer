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

/**
 * Retailer Offer Helper
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Settings extends \Smile\Retailer\Helper\Settings
{
    /**
     * Check if we should enforce filtering on the current retailer (and even pickup date) for navigation in Front Office.
     *
     * @return bool
     */
    public function isNavigationFilterApplied()
    {
        return (bool) $this->getNavigationSettings('enforce_navigation_filter');
    }
}
