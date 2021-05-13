<?php
namespace Straitv\DynamicProduct\Block\Adminhtml\Product;
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected function _construct()	{
		$this->_objectId = 'id';
        $this->_blockGroup = 'Straitv_DynamicProduct';
        $this->_controller = 'adminhtml_product';

        parent::_construct();
		$this->buttonList->remove('back');
		$this->buttonList->remove('save');
		$this->buttonList->remove('reset');
		//$this->_formScripts[] = "";
		// $this->addButton(
        //     'import_product',
        //     [
        //         'label' => __('Upload File'),
        //         'class' => 'scalable primary save',
        //         'level' => -1,
        //     ]
        // );
    }
}