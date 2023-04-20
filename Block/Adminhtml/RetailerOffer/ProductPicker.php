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
namespace Smile\RetailerOffer\Block\Adminhtml\RetailerOffer;

use Magento\Backend\Block\Context;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;

/**
 * Product Picker for Retailer Offer Creation form
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ProductPicker extends \Magento\Backend\Block\AbstractBlock
{
    /**
     * @var FormFactory
     */
    private FormFactory $formFactory;

    /**
     * Constructor.
     *
     * @param Context     $context     Block context.
     * @param FormFactory $formFactory Form factory.
     * @param array       $data        Additional data.
     */
    public function __construct(
        Context $context,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        parent::__construct($context, $data);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) Method is inherited
     *
     * {@inheritDoc}
     */
    protected function _toHtml(): string
    {
        return $this->escapeJsQuote($this->getForm()->toHtml());
    }

    /**
     * Create the form containing the product picker.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return Form
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
            'label' => __("Product"),
            'required' => true,
            'class' => 'widget-option',
            'note' => __("The product you wish to create an offer for"),
        ];

        $productPickerField = $productPickerFieldset->addField('product_picker', 'label', $data);
        $pickerConfig = [
            'button' => ['open' => __("Select Product ...")],
            'type' => 'Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser',
        ];

        $helperBlock = $this->getLayout()->createBlock(
            'Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser',
            '',
            ['data' => $pickerConfig]
        );
        if ($helperBlock instanceof \Magento\Framework\DataObject) {
            $helperBlock
                ->setConfig($pickerConfig)
                ->setFieldsetId($productPickerFieldset->getId())
                ->prepareElementHtml($productPickerField);
        }

        return $form;
    }
}
