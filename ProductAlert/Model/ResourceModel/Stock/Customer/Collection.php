<?php
namespace Agile\ProductAlert\Model\ResourceModel\Stock\Customer;


class Collection extends \Magento\ProductAlert\Model\ResourceModel\Stock\Customer\Collection
{
     /**
     * join productalert stock data to customer collection
     *
     * @param int $productId
     * @param int $websiteId
     * @return $this
     */
    public function join($productId, $websiteId)
    {
        $this->getSelect()->joinRight(
            ['alert' => $this->getTable('product_alert_stock')],
            'alert.customer_id=e.entity_id',
            ['alert_stock_id', 'add_date', 'send_date', 'send_count', 'status','email']
        );

        $this->getSelect()->where('alert.product_id=?', $productId);
        if ($websiteId) {
            $this->getSelect()->where('alert.website_id=?', $websiteId);
        }
        $this->_setIdFieldName('alert_stock_id');
        $this->addAttributeToSelect('*');

        return $this;
    }
}