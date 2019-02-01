<?php
namespace Agile\ProductAlert\Block\Product;
use Magento\Framework\View\Element\Template;

class Stock extends \Magento\ProductAlert\Block\Product\View\Stock
{
    public function getCurrentProduct()
    {   
        return $this->_registry->registry('current_product');
    }  
    
}