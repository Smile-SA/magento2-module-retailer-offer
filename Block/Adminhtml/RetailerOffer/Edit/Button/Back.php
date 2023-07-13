<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Block\Adminhtml\RetailerOffer\Edit\Button;

/**
 * Back Button for offer edition.
 */
class Back extends AbstractButton
{
    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getUrl('*/*/')),
            'class' => 'back',
            'sort_order' => 10,
        ];
    }
}
