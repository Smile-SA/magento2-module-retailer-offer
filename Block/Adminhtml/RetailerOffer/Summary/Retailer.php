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
namespace Smile\RetailerOffer\Block\Adminhtml\RetailerOffer\Summary;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product\Attribute\Source\Status as StatusSource;
use Magento\Framework\Registry;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Retailer\Api\RetailerRepositoryInterface;
use Smile\RetailerOffer\Block\Adminhtml\RetailerOffer\Summary;

/**
 * Panel to display retailer's summary in the offer edit form
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Retailer extends Summary
{
    /**
     * @var string
     */
    protected $_template = 'retailer-offer/summary/retailer.phtml';

    /**
     * Retailer Repository
     *
     * @var RetailerRepositoryInterface
     */
    private RetailerRepositoryInterface $retailerRepository;

    /**
     * Summary constructor.
     *
     * @param Context                     $context            Application context
     * @param Registry                    $registry           Application registry
     * @param RetailerRepositoryInterface $retailerRepository Retailer Repository
     * @param array                       $data               Block's data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RetailerRepositoryInterface $retailerRepository,
        array $data = []
    ) {
        $this->retailerRepository = $retailerRepository;

        parent::__construct($context, $registry, $data);
    }

    /**
     * Get current retailer : retailer of the current offer.
     *
     * @return RetailerInterface
     */
    public function getRetailer(): RetailerInterface
    {
        $retailer = null;

        if ($offer = $this->getRetailerOffer()) {
            if ($offer->getSellerId()) {
                $retailer = $this->retailerRepository->get((int) $offer->getSellerId());
            }
        }

        return $retailer;
    }

    /**
     * Retrieve Product Status Label
     *
     * @return string
     */
    public function getRetailerStatusLabel(): string
    {
        $statusesLabels = [0 => __("Inactive"), 1 => __("Active")];

        return isset($statusesLabels[(int) $this->getRetailer()->getIsActive()]) ? $statusesLabels[(int) $this->getRetailer()->getIsActive()] : "";
    }

    /**
     * Prepare Layout. Overridden to prevent triggering the parent one.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) Method is inherited
     *
     * @return $this
     */
    protected function _prepareLayout(): self
    {
        return $this;
    }
}
