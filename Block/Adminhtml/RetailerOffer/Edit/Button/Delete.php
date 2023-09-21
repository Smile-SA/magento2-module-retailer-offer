<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Block\Adminhtml\RetailerOffer\Edit\Button;

/**
 * Delete button for offer edition.
 */
class Delete extends AbstractButton
{
    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getRetailerOffer() && $this->getRetailerOffer()->getOfferId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }

        return $data;
    }

    /**
     * Get the deletion url.
     */
    private function getDeleteUrl(): string
    {
        return $this->getUrl('*/*/delete', ['offer_id' => $this->getRetailerOffer()->getOfferId()]);
    }
}
