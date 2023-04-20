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
use Magento\Backend\Model\View\Result\Page;
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
     * @var ?PageFactory
     */
    protected ?PageFactory $resultPageFactory = null;

    /**
     * @var ?ForwardFactory
     */
    protected ?ForwardFactory $resultForwardFactory = null;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected Registry $coreRegistry;

    /**
     * @var OfferRepository
     */
    protected OfferRepository $offerRepository;

    /**
     * Retailer Factory
     *
     * @var OfferFactory
     */
    protected OfferFactory $offerFactory;

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
     * @return Page
     */
    protected function createPage(): Page
    {
        /** @var Page $resultPage */
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
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Smile_RetailerOffer::retailer_offers');
    }
}
