<?php
namespace Straitv\DynamicProduct\Block\Adminhtml\Product\Edit;
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		parent::_construct();
        $this->setId('product_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Straitv'));
    }
	protected function _prepareLayout(){		
        $this->addTab(
            'samplecsv',
            [
                'label' => __('Dynamic Product'),
                'title' => __('Dynamic Product'),
                'content' => $this->getLayout()->createBlock(
                    'Straitv\DynamicProduct\Block\Adminhtml\Product\Edit\Tab\Trigger'
                )->toHtml(),
                'active' => true
            ]
        );
		return parent::_prepareLayout();
	}
}