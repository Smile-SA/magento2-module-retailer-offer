<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Aurelien Foucret <aurelien.foucret@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\RetailerOffer\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Smile\Offer\Api\OfferRepositoryInterface as OfferRepository;
use Smile\Offer\Api\Data\OfferInterfaceFactory as OfferFactory;

/**
 * Abstract Controller for retailer offer management.
 *
 * @category Smile
 * @package  Smile\Retailer
 * @author   Aurelien Foucret <aurelien.foucret@smile.fr>
 */
abstract class AbstractOffer extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory|null
     */
    protected $resultPageFactory = null;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory|null
     */
    protected $resultForwardFactory = null;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var \Smile\Offer\Api\OfferRepositoryInterface
     */
    protected $offerRepository;

    /**
     * Retailer Factory
     *
     * @var \Smile\Offer\Api\Data\OfferInterfaceFactory
     */
    protected $offerFactory;

    /**
     * Abstract constructor.
     *
     * @param Context         $context              Application context
     * @param PageFactory     $resultPageFactory    Result Page factory
     * @param ForwardFactory  $resultForwardFactory Result forward factory
     * @param Registry        $coreRegistry         Application registry
     * @param OfferRepository $offerRepository      Offer Repository
     * @param OfferFactory    $offerFactory         Offer Factory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Registry $coreRegistry,
        OfferRepository $offerRepository,
        OfferFactory $offerFactory
    ) {
        $this->resultPageFactory    = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->coreRegistry         = $coreRegistry;
        $this->offerRepository      = $offerRepository;
        $this->offerFactory         = $offerFactory;

        parent::__construct($context);
    }

    /**
     * Create result page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function createPage()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Smile_Retailer::retailer_offers')
            ->addBreadcrumb(__('Sellers'), __('Retailers'), __('Offers'));

        return $resultPage;
    }

    /**
     * Check if allowed to manage offer
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Smile_RetailerOffer::retailer_offers');
    }
}
