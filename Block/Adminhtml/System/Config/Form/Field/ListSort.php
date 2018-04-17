<?php
namespace Yereone\AdvancedSorting\Block\Adminhtml\System\Config\Form\Field;

class ListSort extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
	protected $sortingOptionsRenderer;
	
	/**
	 * @return \Magento\Framework\View\Element\BlockInterface
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	protected function _getSortingOptionsRenderer()
	{
		if (!$this->sortingOptionsRenderer) {
			$this->sortingOptionsRenderer = $this->getLayout()->createBlock(
				'\Yereone\AdvancedSorting\Block\Adminhtml\System\Config\Options\SortingOptions',
				'',
				['data' => ['is_render_to_js_template' => true]]
			);
		}
		return $this->sortingOptionsRenderer;
	}
	
	protected function _prepareToRender()
	{
		$this->addColumn(
			'sorting_option',
			[
				'label' => __('Sorting Option'),
				'renderer' => $this->_getSortingOptionsRenderer()
			]
		);
		$this->addColumn('label', ['label' => __('Label')]);
		
		$this->_addAfter = false;
		$this->_addButtonLabel = __('Add Sorting Option');
	}
	
	/**
	 * @param \Magento\Framework\DataObject $row
	 */
	protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
	{
		$options = [];
		$customAttribute = $row->getData('sorting_option');
		
		$key = 'option_' . $this->_getSortingOptionsRenderer()->calcOptionHash($customAttribute);
		$options[$key] = 'selected="selected"';
		$row->setData('option_extra_attrs', $options);
	}
}