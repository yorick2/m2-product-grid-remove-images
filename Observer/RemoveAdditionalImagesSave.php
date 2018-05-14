<?php
namespace Paulmillband\ProductGridRemoveAdditionalImages\Observer;

use Magento\Backend\App\Action;

/**
 * Class Save
 */
 class RemoveAdditionalImagesSave
    implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var  \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;

    /**
     * @var      \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var
     */
    protected $productCollection;

     /**
      *  @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute
      */
     protected $attributeHelper;


     protected $request;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
    ) {
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->messageManager = $messageManager;
        $this->attributeHelper = $attributeHelper;
        $this->request = $request;
    }

     /**
      * @return \Magento\Framework\App\Request\Http
      */
     protected function getRequest()
     {
         return $this->request;
     }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getProductCollection(){
        if(!$this->productCollection){
            $this->productCollection = $this->attributeHelper->getProducts();
        }
        return $this->productCollection;
    }
//
//    /**
//     * @param array $categoryIds
//     */
//    public function addProductToCategory($categoryIds=[]){
//        if(!count($categoryIds)){
//            return;
//        }
//        foreach($this->getProductCollection() as $product) {
//            $categoryIdsArray = array_unique(
//                array_merge($categoryIds, $product->getCategoryIds()),
//                SORT_STRING
//            );
//            $this->categoryLinkManagement->assignProductToCategories(
//                $product->getSku(),
//                $categoryIdsArray
//            );
//        }
//    }

    /**
     * @param array $categoryIds
     */
    public function removeAdditionalImagesFromProduct($product){
	    $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
	    foreach ($existingMediaGalleryEntries as $key => $entry) {
		    //We can add your condition here
		    unset($existingMediaGalleryEntries[$key]);
	    }
	    $product->setMediaGalleryEntries($existingMediaGalleryEntries);
//	    $this->productRepository->save($product);
    }

     /**
      * @param \Magento\Framework\Event\Observer $observer
      * @return mixed
      */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->getProductCollection()) {
            return ;
        }
        if($this->getRequest()->getParam('remove_additional_images') === null){
			return ;
        }
        try {
	        foreach($this->getProductCollection() as $product) {
	            $this->removeAdditionalImagesFromProduct($product);
	        }
            $this->messageManager
                ->addSuccess(__(
                    'A total of %1 record(s) were updated.',
                    count($this->attributeHelper->getProductIds())
                ));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while updating the product(s) categories.')
            );
        }
    }
}