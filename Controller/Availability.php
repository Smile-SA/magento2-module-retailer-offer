<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Store availability controller.
 */
class Availability extends Action implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private PageFactory $resultPageFactory,
        private ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (!$productId) {
            $resultForward = $this->resultForwardFactory->create();

            return $resultForward->forward('noroute');
        }

        return $this->resultPageFactory->create();
    }
}
