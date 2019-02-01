<?php
namespace Agile\ProductAlert\Model;
use Magento\Framework\View\Element\Template;

class Observer extends \Magento\ProductAlert\Model\Observer
{
     /**
     * Process stock emails
     *
     * @param \Magento\ProductAlert\Model\Email $email
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function _processStock(\Magento\ProductAlert\Model\Email $email)
    {
        $email->setType('stock');

        foreach ($this->_getWebsites() as $website) {
            /* @var $website \Magento\Store\Model\Website */

            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }
            if (!$this->_scopeConfig->getValue(
                self::XML_PATH_STOCK_ALLOW,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $website->getDefaultGroup()->getDefaultStore()->getId()
            )
            ) {
                continue;
            }
            try {
                $collection = $this->_stockColFactory->create()->addWebsiteFilter(
                    $website->getId()
                )->addStatusFilter(
                    0
                )->setCustomerOrder();
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                throw $e;
            }

            $previousCustomer = null;
            $email->setWebsite($website);
            foreach ($collection as $alert) {
                try {
                    if($alert->getCustomerId() != 0){
                        if (!$previousCustomer || $previousCustomer->getId() != $alert->getCustomerId()) {
                            $customer = $this->customerRepository->getById($alert->getCustomerId());
                            if ($previousCustomer) {
                                $email->send();
                            }
                            if (!$customer) {
                                continue;
                            }
                            $previousCustomer = $customer;
                            $email->clean();
                            $email->setCustomerData($customer);
                        } else {
                            $customer = $previousCustomer;
                        }

                        $product = $this->productRepository->getById(
                            $alert->getProductId(),
                            false,
                            $website->getDefaultStore()->getId()
                        );

                        $product->setCustomerGroupId($customer->getGroupId());
                    }
                    else{
                        $email->guestsend($alert->getEmail());

                        $product = $this->productRepository->getById(
                            $alert->getProductId(),
                            false,
                            $website->getDefaultStore()->getId()
                        );
                    }
                    if ($product->isSalable()) {
                        $email->addStockProduct($product);

                        $alert->setSendDate($this->_dateFactory->create()->gmtDate());
                        $alert->setSendCount($alert->getSendCount() + 1);
                        $alert->setStatus(1);
                        $alert->save();
                    }
                } catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                    throw $e;
                }
            }

            if ($previousCustomer) {
                try {
                    $email->send();
                } catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                    throw $e;
                }
            }
        }

        return $this;
    }
}