<?php
namespace Yereone\AdvancedSorting\Model\Config\Source;

class ListSort implements \Magento\Framework\Option\ArrayInterface
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
	public function __construct(\Yereone\AdvancedSorting\Helper\Data $dataHelper)
	{
		$this->_dataHelper = $dataHelper;
	}
	
	/**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
	public function toOptionArray()
	{
		return $this->_dataHelper->getOptionsUsedForSort();
	}
}
