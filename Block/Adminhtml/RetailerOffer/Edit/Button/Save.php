<?php

namespace Smile\RetailerOffer\Block\Adminhtml\RetailerOffer\Edit\Button;

/**
 * Save Button for offer edition.
 */
class Save extends AbstractButton
{
    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'save'],
                ],
            ],
            'sort_order' => 80,
        ];
    }
}
