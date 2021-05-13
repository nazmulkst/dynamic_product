<?php
namespace Straitv\DynamicProduct\Controller\Adminhtml\Product;

use Magento\Framework\App\ObjectManager;
use Straitv\DynamicProduct\Helper\Data;

class Create extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Straitv_DynamicProduct::admin_menu';

    const PAGE_TITLE = 'Create Product';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Straitv\DynamicProduct\Helper\Data
     */
    // protected $_helper;

    // /**
    //  * @param \Magento\Backend\App\Action\Context $context
    //  */
    public function __construct(
       \Magento\Backend\App\Action\Context $context,
       \Magento\Framework\View\Result\PageFactory $pageFactory,
       \Magento\Store\Model\StoreManagerInterface $storeManager
    //    \Straitv\DynamicProduct\Helper\Data $helper
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_storeManager = $storeManager;
        // $this->_helper = $helper;
        return parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $post = $this->getRequest()->getPost();
        // print_r($post);
        $helper = ObjectManager::getInstance()->create(Data::class);
        if(isset($post) && !empty($post)){
            if(isset($post['create_product']) && $post['create_product'] == true){
                try{
                    $helper->createNewProduct();
                } catch(\Exception $e){
                    $helper->logMessage("Create New Product Error: ". $e->getMessage());
                }
            }
            if(isset($post['update_product']) && $post['update_product'] == true){
                try{
                    $helper->updateExistingProduct();
                } catch(\Exception $e){
                    $helper->logMessage("Update Existing Product Error: ". $e->getMessage());
                }
            }
        }
        return;
        // print_r($post);
        // if(isset($post)){
        //     // echo "Product Created";
        //     // $url = "https://sample.hww/rest";
        //     echo $url = $this->_storeManager->getStore()->getBaseUrl();
        //     // return;
        //     $token_url= $url."rest/V1/integration/admin/token";

        //     $username= "admin";
        //     $password= "admin123";

        //     //Authentication REST API magento 2,    
        //     $ch = curl_init();
        //     $data = array("username" => $username, "password" => $password);
        //     $data_string = json_encode($data);

        //     $ch = curl_init();
        //     curl_setopt($ch,CURLOPT_URL, $token_url);
        //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        //     curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //     curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //         'Content-Type: application/json'
        //         ));
        //     $token = curl_exec($ch);
        //     $adminToken=  json_decode($token);
        //     echo $adminToken . "\n";
        //     // return;
        //     $headers = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
        // }

        if(isset($post['create_product']) && $post['create_product'] == true){
            // Createt Product REST API URL
            echo $apiUrl = $url . "rest/V1/products";

            $ch = curl_init();
            $data = [
                "product" => [
                    "sku" => "TestProduct1",
                    "name" => "Test Product 1",
                    "attribute_set_id" => 4,
                    "price" => 99,
                    "status" => 1,
                    "visibility" => 4,
                    "type_id" => "simple",
                    "weight" => "1",
                    "extension_attributes" => [
                        "category_links" => [
                            [
                                "position" => 0,
                                "category_id" => "5"
                            ],
                            [
                                "position" => 1,
                                "category_id" => "7"
                            ]
                        ],
                        "stock_item" => [
                            "qty" => "1000",
                            "is_in_stock" => true
                        ]
                    ],
                    "custom_attributes" => [
                        [
                            "attribute_code" => "description",
                            "value" => "Description of product here"
                        ],
                        [
                            "attribute_code" => "short_description",
                            "value" => "short description of product"
                        ]
                    ]
                ]
            ];
            $data_string = json_encode($data);
            // $data_string = "var/import/products_import.json";
            print_r($data_string);

            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);

            $response = json_decode($response, TRUE);
            print_r($response);
            curl_close($ch);
        }

        if(isset($post['update_product'])){
            echo "Product Updated";
        }
        return;
         /** @var \Magento\Framework\View\Result\Page $resultPage */
         $resultPage = $this->_pageFactory->create();
         $resultPage->setActiveMenu(static::ADMIN_RESOURCE);
         $resultPage->addBreadcrumb(__(static::PAGE_TITLE), __(static::PAGE_TITLE));
         $resultPage->getConfig()->getTitle()->prepend(__(static::PAGE_TITLE));

         return $resultPage;
    }

    /**
     * Is the user allowed to view the page.
    *
    * @return bool
    */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(static::ADMIN_RESOURCE);
    }
}
