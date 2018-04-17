<?php
namespace Yereone\AdvancedSorting\Model\Config\Source;

class BestsellersPeriod implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'yearly', 'label' => __('Yearly')],
            ['value' => 'monthly', 'label' => __('Monthly')],
            ['value' => 'daily', 'label' => __('Daily')]
        ];
    }
}
