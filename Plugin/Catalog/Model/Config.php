<?php
namespace Yereone\AdvancedSorting\Plugin\Catalog\Model;

class Config
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
	 * @param \Magento\Catalog\Model\Config $catalogConfig
	 * @param array $options
	 * @return array
	 */
    public function afterGetAttributeUsedForSortByArray(
    	\Magento\Catalog\Model\Config $catalogConfig,
    	$options
    ) {
    	if (!$this->_dataHelper->isExtentionEnabled()) return $options;
    	return $this->_dataHelper->mergeOptions($options);
    }
}