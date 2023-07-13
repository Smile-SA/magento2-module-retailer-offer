<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Block\Adminhtml\RetailerOffer;

use Magento\Backend\Block\AbstractBlock;
use Magento\Backend\Block\Context;
use Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Product Picker for Retailer Offer Creation form.
 */
class ProductPicker extends AbstractBlock
{
    public function __construct(
        Context $context,
        private FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        return $this->getForm()->toHtml();
    }

    /**
     * Create the form containing the product picker.
     *
     * @throws LocalizedException
     */
    private function getForm(): Form
    {
        $form = $this->formFactory->create();
        $form->setHtmlId('offer_product_picker');

        $productPickerFieldset = $form->addFieldset(
            'offer_product_picker',
            [
                'name' => 'offer_product_picker',
                'label' => __('Product Picker'),
                'container_id' => 'offer_product_picker',
                'class' => 'admin__fieldset offer_product_picker__fieldset',
            ]
        );

        $data = [
            'name'  => 'product_id',
            'label' => __('Product'),
            'required' => true,
            'class' => 'widget-option',
            'note' => __('The product you wish to create an offer for'),
        ];

        $productPickerField = $productPickerFieldset->addField('product_picker', 'label', $data);
        $pickerConfig = [
            'button' => ['open' => __("Select Product ...")],
            'type' => Chooser::class,
        ];

        /** @var Chooser $helperBlock */
        $helperBlock = $this->getLayout()->createBlock(
            Chooser::class,
            '',
            ['data' => $pickerConfig]
        );
        if ($helperBlock instanceof DataObject) {
            $helperBlock->setConfig($pickerConfig);
            $helperBlock->setFieldsetId($productPickerFieldset->getId());
            $helperBlock->prepareElementHtml($productPickerField);
        }

        return $form;
    }
}
