<?php
namespace Paulmillband\ProductGridRemoveImages\Observer;

use Magento\Backend\App\Action;

/**
 * Class Save
 */
 class RemoveImagesSave
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
	  * @var 'on'|null
	  */
     protected $removeBaseImages;

	 /**
	  * @var 'on'|null
	  */
     protected $removeSmallImages;

	 /**
	  * @var 'on'|null
	  */
     protected $removeThumbnailImages;

	 /**
	  * @var 'on'|null
	  */
     protected $removeAdditionalImages;

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
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
    ) {
	    $this->productRepository = $productRepository;
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
            $this->productCollection = $this->attributeHelper
	            ->getProducts()
	            ->addFieldToSelect("image")
	            ->addFieldToSelect("small_image")
	            ->addFieldToSelect("thumbnail")
	            ->addMediaGalleryData();
        }
        return $this->productCollection;
    }

    protected function isDeleteImage($image){
    	$types = $image->getTypes();
    	if (in_array('image', $types) && $this->removeBaseImages !== 'on') {
    		return false;
	    }
    	if (in_array('small', $types) && $this->removeSmallImages !== 'on' ) {
    		return false;
	    }
    	if (in_array('thumbnail', $types) && $this->removeThumbnailImages !== 'on' ) {
    		return false;
	    }
    	if (!count($types) && $this->removeAdditionalImages !== 'on') {
    		return false;
	    }
	   return true;
    }

	 /**
	  * @param $product
	  * @throws \Magento\Framework\Exception\CouldNotSaveException
	  * @throws \Magento\Framework\Exception\InputException
	  * @throws \Magento\Framework\Exception\StateException
	  */
	  protected function removeImagesFromProduct($product){
	    $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
	    foreach ($existingMediaGalleryEntries as $key => $entry) {
		    if ($this->isDeleteImage($entry)) {
		    	unset($existingMediaGalleryEntries[$key]);
		    }
	    }
	    $product->setMediaGalleryEntries($existingMediaGalleryEntries);
	    $this->productRepository->save($product);
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
	    $this->removeBaseImages = $this->getRequest()->getParam('remove_base_images');
	    $this->removeSmallImages = $this->getRequest()->getParam('remove_small_images') ;
	    $this->removeThumbnailImages = $this->getRequest()->getParam('remove_thumbnail_images');
	    $this->removeAdditionalImages = $this->getRequest()->getParam('remove_additional_images');
        if(
	        $this->removeBaseImages === null &&
	        $this->removeSmallImages === null &&
	        $this->removeThumbnailImages === null &&
	        $this->removeAdditionalImages === null
        ){
			return ;
        }
        try {
	        foreach($this->getProductCollection() as $product) {
	            $this->removeImagesFromProduct($product);
	        }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while updating the product(s) gallery images.')
            );
        }
    }
}
