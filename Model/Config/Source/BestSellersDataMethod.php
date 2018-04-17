<?php
namespace Yereone\AdvancedSorting\Model\Config\Source;

class BestSellersDataMethod implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'real_time', 'label' => __('Real Time')],
            ['value' => 'magento_reports', 'label' => __('Magento Reports')]
        ];
    }
}
