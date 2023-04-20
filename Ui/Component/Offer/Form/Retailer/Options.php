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
namespace Smile\RetailerOffer\Ui\Component\Offer\Form\Retailer;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\App\RequestInterface;
use Smile\Retailer\Model\ResourceModel\Retailer\Collection as RetailerCollection;

/**
 * Source Model for Retailer Picker
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Options implements OptionSourceInterface
{
    /**
     * @var RetailerCollection
     */
    protected RetailerCollection $retailerCollection;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var ?array
     */
    protected ?array $retailersList = null;

    /**
     * @param RetailerCollection    $retailerCollection        The Retailer Collection Factory
     * @param RequestInterface      $request                   The application request
     */
    public function __construct(
        RetailerCollection $retailerCollection,
        RequestInterface $request
    ) {
        $this->retailerCollection = $retailerCollection;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        return $this->getRetailerList();
    }

    /**
     * Retrieve retailer tree
     *
     * @return array
     */
    protected function getRetailerList(): array
    {
        if ($this->retailersList === null) {
            $this->retailersList = [];
            $storeId = $this->request->getParam('store');

            $this->retailerCollection
                ->addAttributeToSelect(['name', 'is_active'])
                ->setStoreId($storeId);

            foreach ($this->retailerCollection as $retailer) {
                $this->retailersList[$retailer->getId()] = [
                    'value'     => $retailer->getId(),
                    'is_active' => $retailer->getIsActive(),
                    'label'     => $retailer->getName(),
                ];
            }
        }

        return $this->retailersList;
    }
}
