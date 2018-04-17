<?php 
namespace Yereone\AdvancedSorting\Block\Adminhtml\System\Config\Options;

class SortingOptions extends \Magento\Framework\View\Element\Html\Select
{
	/**
	 * 
	 * @var \Yereone\AdvancedSorting\Model\Config\Source\ListSort
	 */
	protected $_listSort;
	
	/**
	 * 
	 * @param \Magento\Framework\View\Element\Context $context
	 * @param \Yereone\AdvancedSorting\Model\Config\Source\ListSort $listSort
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Context $context,
		\Yereone\AdvancedSorting\Model\Config\Source\ListSort $listSort,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_listSort = $listSort;
	}
	
	/**
	 * 
	 * @param $value
	 * @return mixed
	 */
	public function setInputName($value)
	{
		return $this->setName($value);
	}
	
	/**
	 * Parse to html.
	 *
	 * @return mixed
	 */
	public function _toHtml()
	{
		if (!$this->getOptions()) {
			$options = $this->_listSort->toOptionArray();
			foreach ($options as $option) {
				$this->addOption($option['value'], $option['label']);
			}
		}
		return parent::_toHtml();
	}
}