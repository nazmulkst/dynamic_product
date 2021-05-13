<?php
namespace Straitv\DynamicProduct\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::INFO;

    protected $fileName = '/var/log/product_update.log';
}