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

namespace Smile\RetailerOffer\Ui\Component\Offer\Form\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Smile\Offer\Model\ResourceModel\Offer\Collection;

/**
 * Data Provider for Retailer Offer Edit Form
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Create extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * DataProvider constructor.
     *
     * @param string                   $name              The name
     * @param string                   $primaryFieldName  Primary field Name
     * @param string                   $requestFieldName  Request field Name
     * @param mixed                    $collectionFactory The collection factory
     * @param array                    $meta              Component Meta
     * @param array                    $data              Component Data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        mixed $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Retrieve Current data
     *
     * @return null
     */
    public function getData(): null
    {
        return null;
    }
}
