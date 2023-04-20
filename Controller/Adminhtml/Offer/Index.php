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

namespace Smile\RetailerOffer\Controller\Adminhtml\Offer;

use Magento\Backend\Model\View\Result\Page;
use Smile\RetailerOffer\Controller\Adminhtml\AbstractOffer;

/**
 * Abstract Controller for retailer offer management.
 *
 * @category Smile
 * @package  Smile\Retailer
 * @author   Aurelien Foucret <aurelien.foucret@smile.fr>
 */
class Index extends AbstractOffer
{
    /**
     * {@inheritdoc}
     */
    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Retailer Offers List'));

        return $resultPage;
    }
}
