<?php
namespace Straitv\DynamicProduct\Model;

use Straitv\DynamicProduct\Api\ProductCreateManagementInterface;

class ProductCreateManagement implements ProductCreateManagementInterface {
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
     * @param \Straitv\DynamicProduct\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\Filesystem\Io\File $file,
        \Straitv\DynamicProduct\Logger\Logger $logger
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->_resource = $resource;
        $this->_dir = $dir;
        $this->_file = $file;
        $this->logger = $logger;
    }

    /**
     * Updates the specified product from the request payload.
     *
     * @api
     * @param mixed $products
     * @return boolean
     */
    public function createProduct($products) {
        if (!empty($products)) {
            // $this->writeLog(print_r($products, true));
        	$error = false;
            $response = [];
            
            $websiteId = (int) $this->storeManager->getStore()->getWebsiteId();
            $storeId = (int) $this->storeManager->getStore()->getStoreId();

            if($storeId == 1){
                $storeId = 0;
            }

            // $this->writeLog("WebsiteId: " . $websiteId);
            // $this->writeLog("StoreId: " . $storeId);
            foreach ($products as $product) {
                $exist = $this->productFactory->create()->getIdBySku($product['sku']);
                if($exist){
                    // $this->writeLog('id: ' . $exist);
                    $response[] = ['exists' => true, 'sku' => $product['sku']];
                    continue;
                }
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

                    // $this->writeLog("url key: " . $url_key);

                    if($product['image'] != ""){
                        // Get Image from URL
                        $imageUrl = $product['image'];
                        $tmpDir = $this->getMediaDirTmpDir();
                        $this->_file->checkAndCreateFolder($tmpDir);
                        $newFileName = $tmpDir . baseName($imageUrl);
                        $imageName = pathinfo($newFileName)['basename'];
                        // $this->writeLog("new file name: " . $newFileName);
                        
                        $result = $this->_file->read($imageUrl, $newFileName);
                        // $this->writeLog("Result Image: " . $result);
                    }

                    $creatProduct = $this->productFactory->create();
                    $creatProduct->setTypeId("simple"); // type of product you're importing
                    $creatProduct->setStatus($status); // 1 = enabled
                    $creatProduct->setAttributeSetId(4); // In Magento 2.2 attribute set id 4 is the Default attribute set (this may vary in other versions)
                    $creatProduct->setName($name);
                    $creatProduct->setSku($sku);
                    $creatProduct->setPrice($price);
                    $creatProduct->setSpecialPrice($special_price);
                    $creatProduct->setCost($cost_price);
                    if(strpos($imageName, ".jpg", -4) !== false || strpos($imageName, ".png", -4) !== false || strpos($imageName, ".jpeg", -5) !== false){
                        // Add Image From URL
                        $creatProduct->addImageToMediaGallery($this->_dir->getPath('media') . DIRECTORY_SEPARATOR . pathinfo($newFileName)['basename'], array('image', 'small_image', 'thumbnail'), false, false);
                    }
                    // Add Image From Media Directory
                    // $creatProduct->addImageToMediaGallery($this->_dir->getPath('media') . DIRECTORY_SEPARATOR . pathinfo($image)['basename'], array('image', 'small_image', 'thumbnail'), false, false);

                    $creatProduct->setTaxClassId(2); // 0 = None, 2 = Taxable Goods
                    $creatProduct->setDescription($description);
                    $creatProduct->setWeight($weight);
                    $creatProduct->setShortDescription($short_description);
                    $creatProduct->setUrlKey($url_key);
                    $creatProduct->setWebsiteIds(array($websiteId));
                    $creatProduct->setStoreId($storeId);
                    $creatProduct->setVisibility(4); // 4 = Catalog & Search
                    $creatProduct->setStockData(array('manage_stock' => $manage_stock, 'is_in_stock' => $is_in_stock, 'qty' => $qty, 'min_sale_qty' => $min_sale_qty, 'max_sale_qty' => $max_sale_qty));
                    
        	        try {
                        // Save Product
                        $proSave = $this->productRepository->save($creatProduct);
                        $response[] = ['success' => true, 'id' => $creatProduct->getId(), 'sku' => $creatProduct->getSku(), 'name' => $creatProduct->getName()];
                        $this->writeLog('New Product Created, SKU: ' . $sku);

			        } catch (\Magento\Framework\Exception\StateException $e) {
                        $this->writeLog('Cannot save product ' . $e->getMessage());
			            throw new StateException(__('Cannot save product.'));
			        }
				} catch (\Magento\Framework\Exception\LocalizedException $e) {
	                $messages[] = $product['sku'].' =>'.$e->getMessage();
	                $error = true;
	            }
                unset($creatProduct);
            }
            if ($error) {
	            $this->writeLog(implode(" || ",$messages));
	            return false;
	        }
        }

        // $data = json_encode($response, true);
        // $data = print_r($data);
        $this->writeLog(print_r($response, true));
        return $response;
    }

    public function checkIfProductExists($sku, $websiteId)
    {
        $product = $this->productFactory->create();
        // $product->setWebsiteId($websiteId);
        $productId = $product->getIdBySku(trim($sku));
        //echo $productId = $product->getId();
        if ($productId) {
            return $productId;
        } else {
            return false;
        }
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