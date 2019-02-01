<?php
namespace Agile\ProductAlert\Controller\Add;

use Magento\ProductAlert\Controller\Add as AddController;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action as Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\Customer as Customer;
use Magento\Framework\App\RequestInterface;


class Stock extends \Magento\ProductAlert\Controller\Add\Stock
{

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    { 
       /*  $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create('Magento\Customer\Model\Customer');
        $objectManager3 = \Magento\Framework\App\ObjectManager::getInstance();
        $_emailFactory = $objectManager3->create('Magento\ProductAlert\Model\EmailFactory');
        $email = $_emailFactory->create();
        $objectManager2 = \Magento\Framework\App\ObjectManager::getInstance();
        $Observer = $objectManager2->create('Magento\ProductAlert\Model\Observer');
        $Observer->_processStock($email); */

        $post_data = $this->getRequest()->getPostValue();
        $backUrl = $this->getRequest()->getParam(Action::PARAM_NAME_URL_ENCODED);
        $productId = (int)$post_data['product_id'];
        $customerEmail = $post_data['customer_email'];
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$productId) {
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }
           
        try {
            /* @var $product \Magento\Catalog\Model\Product */
            $product = $this->productRepository->getById($productId);
            $userId =  $customer->setWebsiteId(
                    $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)
                        ->getStore()
                        ->getWebsiteId()
                )->loadByEmail($customerEmail)->getId();
            /** @var \Magento\ProductAlert\Model\Stock $model */
            $model = $this->_objectManager->create(\Magento\ProductAlert\Model\Stock::class)
                ->setCustomerId($userId)
                ->setProductId($product->getId())
                ->setEmail($customerEmail)
                ->setWebsiteId(
                    $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)
                        ->getStore()
                        ->getWebsiteId()
                );
            $model->save();
            $this->messageManager->addSuccess(__('Alert subscription has been saved.'));
        } catch (NoSuchEntityException $noEntityException) {
            $this->messageManager->addError(__('There are not enough parameters.'));
            $resultRedirect->setUrl($backUrl);
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t update the alert subscription right now.'));
        }
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }

    /**
     * Check customer authentication for some actions
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        
        return Action::dispatch($request);
    }
}