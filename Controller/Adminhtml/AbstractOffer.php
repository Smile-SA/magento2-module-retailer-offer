<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Smile\Offer\Api\Data\OfferInterfaceFactory as OfferFactory;
use Smile\Offer\Api\OfferRepositoryInterface as OfferRepository;

/**
 * Abstract Controller for retailer offer management.
 */
abstract class AbstractOffer extends Action
{
    public function __construct(
        Context $context,
        protected PageFactory $resultPageFactory,
        protected ForwardFactory $resultForwardFactory,
        protected Registry $coreRegistry,
        protected OfferRepository $offerRepository,
        protected OfferFactory $offerFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Create result page.
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
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Smile_RetailerOffer::retailer_offers');
    }
}
