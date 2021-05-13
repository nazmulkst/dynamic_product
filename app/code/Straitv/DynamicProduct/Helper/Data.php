<?php
namespace Straitv\DynamicProduct\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\Context;
use Straitv\DynamicProduct\Logger\Logger;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Filesystem\DirectoryList;
use Straitv\DynamicProduct\Model\ProductCreateManagement;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterface;


class Data extends AbstractHelper
{
    /**
     * @var State
     */
    protected $_storeManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var DirectoryList
     */
    protected $_dir;

    /**
     * @var ProductCreateManagement
     */
    protected $productCreate;

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    public function __construct(
    Context $context,
    StoreManagerInterface $storeManager,
    Logger $logger,
    DirectoryList $dir,
    ProductCreateManagement $productCreate,
    ResourceConnection $resource,
    EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
        $this->_dir = $dir;
        $this->productCreate = $productCreate;
        $this->_resource = $resource;
        $this->encryptor = $encryptor;  
    }

    public function getConfigData($path){
		$value = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
		return $value;
	}

    public function logMessage($message) {
        $this->logger->info($message);
    }

    public function run(){
        $isEnable = $this->getConfigData('straitv_dynamic_product/settings/enable');

        $this->logMessage('is enabled: ' . $isEnable);

        if($isEnable){
            $this->createNewProduct();
            $this->updateExistingProduct();
            return true;
        }

        return false;
    }

    public function getAdminToken(){
        $url = $this->_storeManager->getStore()->getBaseUrl();
        // return;
        $token_url= $url."rest/V1/integration/admin/token";
        
        // $username= "admin";
        // $password= "admin123";

        $username = $this->getConfigData('straitv_dynamic_product/settings/admin_user');
        $password = $this->getConfigData('straitv_dynamic_product/settings/admin_password');

        $connection = $this->_resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        $tablename = $connection->getTableName('admin_user');

        $query = "SELECT * FROM " . $tablename . " WHERE username='" . $username."' LIMIT 1";

        $result = $connection->fetchAll($query);

        if(!$result){
            // $response[] = ['username' => false, 'message' => 'Username does\'nt exists.'];
            echo "Username <strong>$username</strong> Does not exists. Please provide correct username and password on the Dynamic Product Configuration setion.";
            $this->logMessage("Username $username Does not exists. Please provide correct username and password on the Dynamic Product Configuration setion.");
            return false;
        }


        $this->logMessage("username: $username");
        $this->logMessage("password: " . $password);

        //Authentication REST API magento 2,    
        $ch = curl_init();
        $data = array("username" => $username, "password" => $password);
        $data_string = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $token_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
            ));
        $token = curl_exec($ch);
        $adminToken=  json_decode($token);
        return $adminToken;
    }

    public function createNewProduct(){
        
        $url = $this->_storeManager->getStore()->getBaseUrl();
        $headers = array('Content-Type:application/json','Authorization:Bearer '. $this->getAdminToken());

        // $apiUrl = $url . "rest/V1/products";
        $apiUrl = $url . "rest/V1/products/createProduct";

        $varDir = $this->_dir->getPath('var');

        $importDir = $varDir . '/import';

        if (!is_dir($importDir)) {
            mkdir($importDir, 0777, true);
        }

        $fileName = "products_import.json";

        $data_dir = $importDir . "/" . $fileName;
        // Reading json file content
        $data_string = file_get_contents($data_dir);

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);

        $response = json_decode($response, TRUE);
        if(isset($response) && !empty($response)){
            foreach($response as $item){
                if(isset($item['success'])){
                    echo "<p>Created New Product SKU: <strong>{$item['sku']}</strong></p>";
                }
                if(isset($item['exists'])){
                    echo "<p>Product Already Exists SKU: <strong>{$item['sku']}</strong></p>";
                }
            }
        }
        // print_r($response);

        // $this->logMessage(print_r($response));
        curl_close($ch);
        return $this;

    }

    public function updateExistingProduct(){
        $url = $this->_storeManager->getStore()->getBaseUrl();
        $headers = array('Content-Type:application/json','Authorization:Bearer '. $this->getAdminToken());

        // $apiUrl = $url . "rest/V1/products";
        $apiUrl = $url . "rest/V1/products/updateProduct";

        $varDir = $this->_dir->getPath('var');

        $importDir = $varDir . '/import';

        if (!is_dir($importDir)) {
            mkdir($importDir, 0777, true);
        }

        $fileName = "products_update.json";

        $data_dir = $importDir . "/" . $fileName;
        // Reading json file content
        $data_string = file_get_contents($data_dir);

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);

        $response = json_decode($response, TRUE);
        if(isset($response) && !empty($response)){
            foreach($response as $item){
                if(isset($item['success'])){
                    echo "<p>Product Successfully Updated SKU: <strong>{$item['sku']}</strong></p>";
                }
                if(isset($item['error'])){
                    echo "<p>Error: <strong>{$item['message']}</strong> for SKU: <strong>{$item['sku']}</strong></p>";
                }
            }
        }
        // print_r($response);

        // $this->logMessage(print_r($response));
        curl_close($ch);
        return $this;
    }
}
