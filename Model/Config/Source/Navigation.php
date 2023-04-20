<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Navigation
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Navigation implements ArrayInterface
{
    /**
     * Constant value for "Retail" mode
     */
    const RETAIL_MODE = 0;

    /**
     * Constant value for "Drive" mode
     */
    const DRIVE_MODE = 1;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [['value' => 0, 'label' => __('Retail')], ['value' => 1, 'label' => __('Drive')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [0 => __('Retail'), 1 => __('Drive')];
    }
}
