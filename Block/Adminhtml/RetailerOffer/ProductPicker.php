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
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * Constructor.
     *
     * @param \Magento\Backend\Block\Context      $context     Block context.
     * @param \Magento\Framework\Data\FormFactory $formFactory Form factory.
     * @param array                               $data        Additional data.
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
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
    protected function _toHtml()
    {
        return $this->escapeJsQuote($this->getForm()->toHtml());
    }

    /**
     * Create the form containing the product picker.
     *
     * @return \Magento\Framework\Data\Form
     */
    private function getForm()
    {
        $form = $this->formFactory->create();
        $form->setHtmlId('offer_product_picker');

        $productPickerFieldset = $form->addFieldset(
            'offer_product_picker',
            ['name' => 'offer_product_picker', 'label' => __('Product Picker'), 'container_id' => 'offer_product_picker']
        );

        $data = [
            'name'  => 'offer_product_picker[product_id]',
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
