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
namespace Smile\RetailerOffer\Ui\Component\Offer\Listing;

use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Data Provider for UI Retailer Offer
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var AddFieldToCollectionInterface[]
     */
    private array $addFieldStrategies;

    /**
     * @var AddFilterToCollectionInterface[]
     */
    private array $addFilterStrategies;

    /**
     * @var mixed
     */
    protected mixed $collectionFactory;

    /**
     * Construct
     *
     * @param string                            $name                Component name
     * @param string                            $primaryFieldName    Primary field Name
     * @param string                            $requestFieldName    Request field name
     * @param mixed                             $collectionFactory   The collection factory
     * @param AddFieldToCollectionInterface[]   $addFieldStrategies  Add field Strategy
     * @param AddFilterToCollectionInterface[]  $addFilterStrategies Add filter Strategy
     * @param array                             $meta                Component Meta
     * @param array                             $data                Component extra data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        $collectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();

        $this->collection->addFilterToMap('offer_id', 'main_table.offer_id');
        $this->addFieldStrategies  = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    /**
     * Add field to select
     *
     * @param string|array $field The field
     * @param string|null  $alias Alias for the field
     *
     * @return void
     */
    public function addField($field, $alias = null): void
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        }
        if (!isset($this->addFieldStrategies[$field])) {
            parent::addField($field, $alias);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(Filter $filter): void
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        }
        if (!isset($this->addFilterStrategies[$filter->getField()])) {
            parent::addFilter($filter);
        }
    }
}
