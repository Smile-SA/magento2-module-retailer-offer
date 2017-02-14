<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticSuite________
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * _________________________________________________
 *
 * @category Smile
 * @package  Smile\ElasticSuite______________
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Availability extends Action
{
    /**
     * Page factory.
     *
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * Forward factory.
     *
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * Constructor.
     *
     * @param Context        $context        Application Context
     * @param PageFactory    $pageFactory    Result Page Factory
     * @param ForwardFactory $forwardFactory Forward Factory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ForwardFactory $forwardFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $pageFactory;
        $this->resultForwardFactory = $forwardFactory;
    }

    /**
     * {@inheritdoc}
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
