<?php
namespace Straitv\DynamicProduct\Block\Adminhtml\Product\Edit\Tab;
class Trigger extends \Magento\Backend\Block\Widget\Form\Generic
{
	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = array()
    ) {
		$this->setTemplate('Straitv_DynamicProduct::trigger.phtml');
        parent::__construct($context, $registry, $formFactory, $data);
	}	
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}