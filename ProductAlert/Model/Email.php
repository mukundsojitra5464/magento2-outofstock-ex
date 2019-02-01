<?php
namespace Agile\ProductAlert\Model;
use Magento\Framework\View\Element\Template;

class Email extends \Magento\ProductAlert\Model\Email
{
    /**
     * Send customer email
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function guestsend($email)
    {
        if ($this->_website === null) {
            return false;
        }
        if ( $this->_type == 'stock' && count($this->_stockProducts) == 0) {
            return false;
        }
        if (!$this->_website->getDefaultGroup() || !$this->_website->getDefaultGroup()->getDefaultStore()) {
            return false;
        }
        $store = $this->_website->getDefaultStore();
        $storeId = $store->getId();

        if ($this->_type == 'stock' && !$this->_scopeConfig->getValue(
            self::XML_PATH_EMAIL_STOCK_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        )
        ) {
            return false;
        }

        if ($this->_type != 'price' && $this->_type != 'stock') {
            return false;
        }

        $this->_appEmulation->startEnvironmentEmulation($storeId);

        if ($this->_type == 'price') {
            $this->_getPriceBlock()->setStore($store)->reset();
            foreach ($this->_priceProducts as $product) {
               // $product->setCustomerGroupId($this->_customer->getGroupId());
                $this->_getPriceBlock()->addProduct($product);
            }
            $block = $this->_getPriceBlock();
            $templateId = $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_PRICE_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } else {
            $this->_getStockBlock()->setStore($store)->reset();
            foreach ($this->_stockProducts as $product) {
               // $product->setCustomerGroupId($this->_customer->getGroupId());
                $this->_getStockBlock()->addProduct($product);
            }
            $block = $this->_getStockBlock();
            $templateId = $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_STOCK_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        $alertGrid = $this->_appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_FRONTEND,
            [$block, 'toHtml']
        );
        $this->_appEmulation->stopEnvironmentEmulation();

        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $templateId
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            [
                'customerName' => $email,
                'alertGrid' => $alertGrid,
            ]
        )->setFrom(
            $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        )->addTo(
            $email,
            $email
        )->getTransport();

        $transport->sendMessage();

        return true;
    }
}