<?php
namespace Straitv\DynamicProduct\Model;

use Straitv\DynamicProduct\Api\ProductUpdateManagementInterface;

class ProductUpdateManagement implements ProductUpdateManagementInterface {
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @ \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_dir;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_file;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\Processor
     */
    protected $processor;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery
     */
    protected $gallery;

    /**
     * @var \Straitv\DynamicProduct\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Catalog\Model\Product\Gallery\Processor $processor
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $gallery
     * @param \Straitv\DynamicProduct\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Catalog\Model\Product\Gallery\Processor $processor,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $gallery,
        \Straitv\DynamicProduct\Logger\Logger $logger
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->_resource = $resource;
        $this->_dir = $dir;
        $this->_file = $file;
        $this->processor = $processor;
        $this->gallery = $gallery;
        $this->logger = $logger;
    }

    /**
     * Updates the specified product from the request payload.
     *
     * @api
     * @param mixed $products
     * @return boolean
     */
    public function updateProduct($products) {
        if (!empty($products)) {
        	$error = false;
            $response = [];

            $websiteId = (int) $this->storeManager->getStore()->getWebsiteId();
            $storeId = (int) $this->storeManager->getStore()->getStoreId();
            if($storeId == 1){
                $storeId = 0;
            }

            foreach ($products as $product) {
        		try {
        			$sku = $product['sku'];
                    $description = $product['description'];
                    $name = $product['name'];
        	       	$price = $product['price'];
                    $special_price = $product['special_price'];
                    $cost_price = $product['cost_price'];
                    $short_description = $product['short_description'];
                    $status = $product['status'];
                    $weight = $product['weight'];
        	       	$qty = $product['qty'];
                    $min_sale_qty = $product['min_sale_qty'];
                    $max_sale_qty = $product['max_sale_qty'];
                    $is_in_stock = $product['is_in_stock'];
                    $manage_stock = $product['manage_stock'];
                    $url_key = $this->createUrlKey($name, $sku, $storeId);

                    $productObject = $this->productRepository->get($sku);
                    $productObject->setStatus($status); // 1 = enabled
                    $productObject->setName($name);
                    $productObject->setSku($sku);
                    $productObject->setPrice($price);
                    $productObject->setSpecialPrice($special_price);
                    $productObject->setCost($cost_price);
                    
                   
                    // Unset existing images
                    $existingGallery = $productObject->getMediaGalleryImages();
                    $imageExists = $productObject->getData('image');
                    // $this->writeLog($imageExists);
                    // $this->writeLog(print_r($existingGallery));
                    // if(isset($existingGallery)){    
                    //     foreach($existingGallery as $child) {
                    //         $this->gallery->deleteGallery($child->getValueId());
                    //         $this->processor->removeImage($productObject, $child->getFile());
                    //     }
                    // }

                    // Update New Images
                    if($product['image'] != ""){
                        $imageUrl = $product['image'];
                        $tmpDir = $this->getMediaDirTmpDir();
                        $this->_file->checkAndCreateFolder($tmpDir);
                        $newFileName = $tmpDir . baseName($imageUrl);
                        $fileExists = file_exists($newFileName);
                        $imageName = pathinfo($newFileName)['basename'];
                        $result = $this->_file->read($imageUrl, $newFileName);
                        // $this->writeLog('File Exists: ' . $fileExists);
                        // $this->writeLog('Image Exists: ' . $imageExists);
                    
                        if($fileExists != 1 || $imageExists == 'no_selection' && strpos($imageName, ".jpg", -4) !== false || strpos($imageName, ".png", -4) !== false || strpos($imageName, ".gif", -4) !== false || strpos($imageName, ".jpeg", -5) !== false){
                            // Add Image From URL
                            $productObject->addImageToMediaGallery($this->_dir->getPath('media') . DIRECTORY_SEPARATOR . pathinfo($newFileName)['basename'], array('image', 'small_image', 'thumbnail'), false, false);
                        }
                    }

                    // Add Image From Media Directory
                    // $creatProduct->addImageToMediaGallery($this->_dir->getPath('media') . DIRECTORY_SEPARATOR . pathinfo($image)['basename'], array('image', 'small_image', 'thumbnail'), false, false);

                    $productObject->setDescription($description);
                    $productObject->setWeight($weight);
                    $productObject->setShortDescription($short_description);
                    $productObject->setWebsiteIds(array($websiteId));
                    $productObject->setStoreId($storeId);
                    $productObject->setStockData(array('manage_stock' => $manage_stock, 'is_in_stock' => $is_in_stock, 'qty' => $qty, 'min_sale_qty' => $min_sale_qty, 'max_sale_qty' => $max_sale_qty));

        	    try {
			        $this->productRepository->save($productObject);

                    $response[] = ['success' => true, 'id' => $productObject->getId(), 'sku' => $productObject->getSku(), 'name' => $productObject->getName()];

                    $this->writeLog('Product Successfully Updated SKU ' . $sku);

			        } catch (\Magento\Framework\Exception\StateException $e) {
                        $this->writeLog('Cannot update product ' . $e->getMessage());
			            throw new StateException(__('Cannot update product.'));
			        }
				} catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->writeLog('Error: ' . $e->getMessage() . " for SKU: " . $sku);
                    $response[] = ['error' => true, 'message' => $e->getMessage(), 'sku' => $product['sku']];
	                $messages[] = $sku.' =>'.$e->getMessage();
	                $error = true;
	            }
            }
            if ($error) {
	            // $this->writeLog(implode(" || ",$messages));
                $this->writeLog(print_r($response, true));
                return $response;
	            // return false;
	        }
        }
        $this->writeLog(print_r($response, true));
        return $response;
    }

    protected function getMediaDirTmpDir()
    {
        return $this->_dir->getPath('media') . DIRECTORY_SEPARATOR . 'tmp';
    }

    public function createUrlKey($name, $sku, $storeId){
        $url = preg_replace('#[^0-9a-z]+#i', '-', $name);
        $urlKey = strtolower($url);
        // return $urlKey . '-' . time();

        $isUnique = $this->checkUrlKeyDuplicates($sku, $urlKey, $storeId);
        if ($isUnique) {
            return $urlKey;
        } else {
            return $urlKey . '-' . time();
        }
    }

    /*
    * Function to check URL Key Duplicates in Database
    */

    private function checkUrlKeyDuplicates($sku, $urlKey, $storeId) 
    {
        $urlKey .= '.html';

        $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

        $tablename = $connection->getTableName('url_rewrite');
        $sql = $connection->select()->from(
                        ['url_rewrite' => $connection->getTableName('url_rewrite')], ['request_path', 'store_id']
                )->joinLeft(
                        ['cpe' => $connection->getTableName('catalog_product_entity')], "cpe.entity_id = url_rewrite.entity_id"
                )->where('request_path IN (?)', $urlKey)
                ->where('store_id IN (?)', $storeId)
                ->where('cpe.sku not in (?)', $sku);

        $urlKeyDuplicates = $connection->fetchAssoc($sql);

        if (!empty($urlKeyDuplicates)) {
            return false;
        } else {
            return true;
        }
    }

    /* log for an API */
    public function writeLog($log)
    {
        $this->logger->info($log);
    }
}