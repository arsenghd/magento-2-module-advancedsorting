<?php
namespace Yereone\AdvancedSorting\Plugin\Catalog\Model\ResourceModel\Product;

class Collection
{
	/**
	 *
	 * @var \Yereone\AdvancedSorting\Helper\Data
	 */
	protected $_dataHelper;
	
	/**
	 * 
	 * @param \Yereone\AdvancedSorting\Helper\Data $dataHelper
	 */
	public function __construct(
		\Yereone\AdvancedSorting\Helper\Data $dataHelper
	) {
		$this->_dataHelper = $dataHelper;
	}
	
	/**
	 * 
	 * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
	 * @param @return \Magento\Framework\DB\Select $countSelect
	 * @return \Magento\Framework\DB\Select
	 */
    public function afterGetSelectCountSql(
    	\Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
    	$countSelect
    ) {
    	if (!$this->_dataHelper->isExtentionEnabled()) return $countSelect;
    	if (count($countSelect->getPart(\Zend_Db_Select::GROUP)) > 0) {
    		$countSelect->reset(\Zend_Db_Select::GROUP);
    	}
    	return $countSelect;
    }
}