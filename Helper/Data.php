<?php 
namespace Yereone\AdvancedSorting\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
	 * Store manager
	 *
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;
	
	/**
	 * 
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	) {
		$this->_storeManager = $storeManager;
		parent::__construct($context);
	}
	
	/**
	 * check if extention enabled
	 *
	 * @return boolean
	 */
	public function isExtentionEnabled()
	{
		return $this->scopeConfig->getValue('advancedSorting/general/active');
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getEnabledSortingOptions()
	{
		$enabledOptions = $this->scopeConfig->getValue('advancedSorting/general/advanced_sorting_options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore());
		return $this->unserialize($enabledOptions);
	}
	
	/**
	 * 
	 * @param string $string
	 * @return array
	 */
	public function unserialize($string)
	{
		try {
			$result = json_decode($string, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				$result = unserialize($string);
			}
		} catch (\Exception $e) {
			throw new \InvalidArgumentException('Unable to unserialize value.');
		}
		return $result;
	}
	
	/**
	 * check if sorting option disabled
	 * 
	 * @return boolean
	 */
	public function isPositionDisabled()
	{
		return $this->scopeConfig->getValue('advancedSorting/general/disable_position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore());
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getBestsellersDataMethod()
	{
	    return $this->scopeConfig->getValue('advancedSorting/general/bestsellers_data_source', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore());
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getBestsellersPeriod()
	{
	    return $this->scopeConfig->getValue('advancedSorting/general/bestsellers_period', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore());
	}
	
	/**
	 *
	 * @return string
	 */
	public function getMostViewedPeriod()
	{
	    return $this->scopeConfig->getValue('advancedSorting/general/most_viewed_period', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore());
	}
	
	/**
	 *
	 * @return string
	 */
	public function getBestsellersDate()
	{
	    switch ($this->getBestsellersPeriod()) {
	        case 'yearly':
	            $date = date('Y-01-01');
	            break;
	        case 'monthly':
	            $date = date('Y-m-01');
	            break;
	        case 'daily':
	            $date = date('Y-m-d');
	            break;
	    }
	    return $date;
	}
	
	/**
	 * 
	 * @param \Magento\Framework\DB\Adapter\AdapterInterface $select
	 * @return \Magento\Framework\DB\Adapter\AdapterInterface
	 */
    public function getBestsellersDateForRealTime($select)
    {
        switch ($this->getBestsellersPeriod()) {
            case 'yearly':
                $dateFrom = date('Y-01-01 00:00:00');
                $dateTo = date('Y-12-31 23:59:59');
                break;
            case 'monthly':
                $dateFrom = date('Y-m-01 00:00:00');
                $dateTo = date('Y-m-31 23:59:59');
                break;
            case 'daily':
                $dateFrom = date('Y-m-d 00:00:00');
                $dateTo = date('Y-m-d 23:59:59');
                break;
        }
        $select->where('sales_order_item.created_at >= ?',$dateFrom);
        $select->where('sales_order_item.created_at <= ?',$dateTo);
        return $select;
    }
    
    /**
	 * 
	 * @param \Magento\Framework\DB\Adapter\AdapterInterface $select
	 * @return \Magento\Framework\DB\Adapter\AdapterInterface
	 */
    public function addPeriodToMostViewed($select)
    {
        $days = (int)trim($this->getMostViewedPeriod());
        if (!$days) return $select;

        $date = strtotime(date('Y-m-d H:i:s'));
        $dateFrom = date('Y-m-d H:i:s',strtotime('-'.$days.' days',$date));
        $select->where('report_event.logged_at >= ?',$dateFrom);
        return $select; 
    }
    
	
	/**
	 * Biggest Saving type
	 * 
	 * @return boolean
	 */
	public function isSavingPercentage()
	{
		return $this->scopeConfig->getValue('advancedSorting/general/saving_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore());
	}
	
	/**
	 * Hide or Show sorting option position
	 * 
	 * @param array $options
	 * @return array
	 */
	public function hideShowPosition($options)
	{
		if ($this->isPositionDisabled()) {
			if (isset($options[0])) {
				unset($options[0]);
			} else {
				unset($options['position']);
			}
		}
		return $options;
	}
	
	/**
	 * Merge enabled advanced options with default sorting attributes
	 * 
	 * @param array $options
	 * @return array
	 */
	public function mergeOptions($options)
	{
		$allOptions = $this->getOptionsUsedForSort();
		foreach ($allOptions as $option) {
			foreach ($this->getEnabledSortingOptions() as $enabledOption) {
				if (in_array($option['value'], $enabledOption)) {
					$label = __($option['label']);
					if ($enabledOption['label'] != '') {
						$label = $enabledOption['label'];
					}
					$options[$option['value']] = $label;
					break;
				}
			}
		}
		return $this->hideShowPosition($options);
	}
	
	/**
	 * Retrieve Options Used for Sort by
	 *
	 * @return array
	 */
	public function getOptionsUsedForSort()
	{
		return [
			["value" => "best_sellers", "label" => __("Best Sellers")],
			["value" => "new", "label" => __("New")],
			["value" => "biggest_saving", "label" => __("Biggest Saving")],
			["value" => "top_rated", "label" => __("Top Rated")],
			["value" => "reviews_count", "label" => __("Reviews Count")],
			["value" => "most_viewed", "label" => __("Most Viewed")]
		];
		 
	}
}