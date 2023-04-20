<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Plugin to ensure the context properly vary according to currently selected (or not) retailer.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ContextPlugin
{
    /**
     * @var CurrentStore
     */
    private CurrentStore $currentStore;

    /**
     * @var Context
     */
    private Context $httpContext;

    /**
     * @var Settings
     */
    private Settings $settingsHelper;

    /**
     * @param Context       $httpContext    HTTP Context
     * @param CurrentStore  $currentStore   The Current Store
     * @param Settings      $settingsHelper RetailerOffer Settings Helper
     */
    public function __construct(
        Context $httpContext,
        CurrentStore $currentStore,
        Settings $settingsHelper
    ) {
        $this->currentStore   = $currentStore;
        $this->httpContext    = $httpContext;
        $this->settingsHelper = $settingsHelper;
    }

    /**
     * Ensure proper vary on frontend according to current Retailer Id (if any)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param ActionInterface  $subject The Action
     * @param \Closure         $proceed The \Magento\Framework\App\Action\Action::dispatch() method
     * @param RequestInterface $request HTTP Request
     *
     * @return mixed
     */
    public function aroundDispatch(
        ActionInterface $subject,
        \Closure $proceed,
        RequestInterface $request
    ): mixed {

        if ($this->settingsHelper->isDriveMode()) {
            // Set a default value to have common vary for all customers without any chosen retailer.
            $retailerId = 'default';

            if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
                $retailerId = $this->currentStore->getRetailer()->getId();
            }

            $this->httpContext->setValue(
                CurrentStore::CONTEXT_RETAILER,
                $retailerId,
                false
            );
        }

        return $proceed($request);
    }
}
