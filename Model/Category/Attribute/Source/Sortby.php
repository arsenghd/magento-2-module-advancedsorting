<?php 
namespace Yereone\AdvancedSorting\Model\Category\Attribute\Source;

class Sortby extends \Magento\Catalog\Model\Category\Attribute\Source\Sortby
{
	/**
	 *
	 * @var \Yereone\AdvancedSorting\Helper\Data
	 */
	protected $_dataHelper;
	
	/**
	 * 
	 * @param \Magento\Catalog\Model\Config $catalogConfig
	 * @param \Yereone\AdvancedSorting\Helper\Data $dataHelper
	 */
	public function __construct(
		\Magento\Catalog\Model\Config $catalogConfig,
		\Yereone\AdvancedSorting\Helper\Data $dataHelper
	) {
		$this->_dataHelper = $dataHelper;
		parent::__construct($catalogConfig);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getAllOptions()
	{
		if (!$this->_dataHelper->isExtentionEnabled()) return parent::getAllOptions();
		if ($this->_options === null) {
			if(!$this->_dataHelper->isPositionDisabled()) {
				$this->_options = [['label' => __('Position'), 'value' => 'position']];
			}
			foreach ($this->_getCatalogConfig()->getAttributesUsedForSortBy() as $attribute) {
				$this->_options[] = [
					'label' => __($attribute['frontend_label']),
					'value' => $attribute['attribute_code'],
				];
			}
			
			$allOptions = $this->_dataHelper->getOptionsUsedForSort();
			foreach ($allOptions as $option) {
				foreach ($this->_dataHelper->getEnabledSortingOptions() as $enabledOption) {
					if (in_array($option['value'], $enabledOption)) {
						$label = __($option['label']);
						if ($enabledOption['label'] != '') {
							$label = $enabledOption['label'];
						}
						$this->_options[] = [
							'label' => $label,
							'value' => $option['value']
						];
						break;
					}
				}
			}
		}
		return $this->_options;
	}
}