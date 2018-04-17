<?php 
namespace Yereone\AdvancedSorting\Block\Product\ProductList;

use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
	/**
	 *
	 * @var \Yereone\AdvancedSorting\Helper\Data
	 */
	protected $_dataHelper;

    /**
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected  $_resource;
	
	/**
	 * 
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Catalog\Model\Session $catalogSession
	 * @param \Magento\Catalog\Model\Config $catalogConfig
	 * @param ToolbarModel $toolbarModel
	 * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
	 * @param ProductList $productListHelper
	 * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
	 * @param \Yereone\AdvancedSorting\Helper\Data $dataHelper
	 * @param \Magento\Framework\App\ResourceConnection $resource
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Catalog\Model\Session $catalogSession,
		\Magento\Catalog\Model\Config $catalogConfig,
		ToolbarModel $toolbarModel,
		\Magento\Framework\Url\EncoderInterface $urlEncoder,
		ProductList $productListHelper,
		\Magento\Framework\Data\Helper\PostHelper $postDataHelper,
		\Yereone\AdvancedSorting\Helper\Data $dataHelper,
        \Magento\Framework\App\ResourceConnection $resource,
		array $data = []
	) {
		$this->_dataHelper = $dataHelper;
		$this->_resource = $resource;
		parent::__construct(
			$context, 
			$catalogSession, 
			$catalogConfig, 
			$toolbarModel, 
			$urlEncoder, 
			$productListHelper, 
			$postDataHelper, 
			$data
		);
	}
	
	/**
	 * Set collection to pager
	 *
	 * @param \Magento\Framework\Data\Collection $collection
	 * @return $this
	 */
	public function setCollection($collection)
	{
		$this->_collection = $collection;
		
		$this->_collection->setCurPage($this->getCurrentPage());
		
		// we need to set pagination only if passed value integer and more that 0
		$limit = (int)$this->getLimit();
		if ($limit) {
			$this->_collection->setPageSize($limit);
		}
		if ($this->getCurrentOrder()) {
			switch ($this->getCurrentOrder()) {
				case 'best_sellers':
					$this->byBestSellers($this->_collection, $this->getCurrentDirection(true));
					break;
				case 'new':
					$this->_collection->setOrder('created_at', $this->getCurrentDirection(true));
					break;
				case 'biggest_saving':
					$expression = "((price_index.price - price_index.final_price) * 100 / price_index.price)";
					if (!$this->_dataHelper->isSavingPercentage()) {
						$expression = "(price_index.price - price_index.final_price)";
					} 
					$key = rand();
					$alias = 'saving'.$key;
					$this->_collection->addExpressionAttributeToSelect($alias, $expression, array());
					$this->_collection->getSelect()->order($alias.' '.$this->getCurrentDirection(true));
					break;
				case 'top_rated':
					$this->byReviewOrRating($this->_collection, 'rating_summary', $this->getCurrentDirection(true));
					break;
				case 'reviews_count':
					$this->byReviewOrRating($this->_collection, 'reviews_count', $this->getCurrentDirection(true));
					break;
				case 'most_viewed':
					$this->byMostView($this->_collection, $this->getCurrentDirection(true));
					break;
				default:
					$this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
					break;
			}
		}
		return $this;
	}
	
	/**
	 * Retrieve current direction
	 *
	 * @return string
	 */
	public function getCurrentDirection($custom_sorting = false)
	{
		if ($custom_sorting) {
			$this->_direction = 'desc';
		}
		$dir = $this->_getData('_current_grid_direction');
		if ($dir) {
			return $dir;
		}
		
		$directions = ['asc', 'desc'];
		$dir = strtolower($this->_toolbarModel->getDirection());
		if (!$dir || !in_array($dir, $directions)) {
			$dir = $this->_direction;
		}
		
		if ($dir != $this->_direction) {
			$this->_memorizeParam('sort_direction', $dir);
		}
		
		$this->setData('_current_grid_direction', $dir);
		return $dir;
	}
	
	/**
	 * Sort by rating or reviews count
	 *
	 * @param \Magento\Framework\Data\Collection $collection
	 * @param string $order
	 * @param string $direction
	 * @return \Magento\Framework\Data\Collection
	 */
	private function byReviewOrRating($collection, $order, $direction)
	{
		$collection->getSelect()->joinLeft(
			'review_entity_summary',
			'e.entity_id = review_entity_summary.entity_pk_value and review_entity_summary.store_id=cat_index.store_id'
				)->group('e.entity_id')->order($order . ' ' . $direction);
		return $collection;
	}
	
	/**
	 * Sort by Best Sellers
	 *
	 * @param \Magento\Framework\Data\Collection $collection
	 * @param string $direction
	 * @return \Magento\Framework\Data\Collection
	 */
	private function byBestSellers($collection, $direction)
	{
	    $bestSellersDataMethod = $this->_dataHelper->getBestsellersDataMethod();
	    if ($bestSellersDataMethod == 'real_time') {
	        $select = $this->_resource->getConnection()->select()->from(
                ['sales_order_item'],
                ['qty_ordered','store_id','created_at','product_id']
            );
	        $this->_dataHelper->getBestsellersDateForRealTime($select);
	        
	        $collection->getSelect()->joinLeft(
                $select,
                'e.entity_id = t.product_id and t.store_id=cat_index.store_id',
	                array('qty_ordered'=>'SUM(t.qty_ordered)'))
	                   ->group('e.entity_id')->order('qty_ordered '.$direction);
	    } else {
	        $bestSellersPeriod = $this->_dataHelper->getBestsellersPeriod();
	        $table = 'sales_bestsellers_aggregated_'.$bestSellersPeriod;
	        $this->getBestSellersByPeriod($collection, $direction, $table, $this->_dataHelper->getBestsellersDate());
	    }
		return $collection;
	}
	
	/**
	 * 
	 * @param \Magento\Framework\Data\Collection $collection
	 * @param string $direction
	 * @param string $table
	 * @param string $date
	 */
	private function getBestSellersByPeriod($collection, $direction, $table, $date)
	{
        $select = $this->_resource->getConnection()->select()->from(
            [$table],
            ['product_id','store_id','qty_ordered', 'period']
        )->where('period=?',$date);

        $collection->getSelect()->joinLeft(
            $select,
            'e.entity_id = t.product_id and t.store_id=cat_index.store_id'
        )->order('t.qty_ordered '. $direction);
	}
	
	/**
	 * Sort by most viewd
	 *
	 * @param \Magento\Framework\Data\Collection $collection
	 * @param string $direction
	 * @return \Magento\Framework\Data\Collection
	 */
	private function byMostView($collection, $direction)
	{
	    $select = $this->_resource->getConnection()->select()->from(
            ['report_event'],
            ['event_id','logged_at','object_id','store_id']
        ); 
	    $this->_dataHelper->addPeriodToMostViewed($select);
	    
		$collection->getSelect()->joinLeft(
	        $select,
			'e.entity_id = t.object_id and t.store_id=cat_index.store_id',
			'COUNT(t.event_id) AS views'
            )->group('e.entity_id')->order('views '.$direction);

		return $collection;
	}
}
