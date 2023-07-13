<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Navigation source.
 */
class Navigation implements ArrayInterface
{
    public const RETAIL_MODE = 0;
    public const DRIVE_MODE = 1;

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 0, 'label' => __('Retail')],
            ['value' => 1, 'label' => __('Drive')],
        ];
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
