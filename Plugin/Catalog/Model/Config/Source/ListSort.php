<?php
namespace Yereone\AdvancedSorting\Plugin\Catalog\Model\Config\Source;

class ListSort
{
	/**
	 *
	 * @var \Yereone\AdvancedSorting\Helper\Data
	 */
	protected $_dataHelper;
	
	/**
	 *
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 */
	public function __construct(
		\Yereone\AdvancedSorting\Helper\Data $dataHelper
	) {
		$this->_dataHelper = $dataHelper;
	}
	
	/**
	 * 
	 * @param \Magento\Catalog\Model\Config\Source\ListSort $listSort
	 * @param $options
	 * @return array
	 */
	public function afterToOptionArray(
		\Magento\Catalog\Model\Config\Source\ListSort $listSort,
    	$options
    ) {
    	if (!$this->_dataHelper->isExtentionEnabled()) return $options;
    	return $this->_dataHelper->mergeOptions($options);
    }
}