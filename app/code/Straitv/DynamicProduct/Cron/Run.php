<?php
namespace Straitv\DynamicProduct\Cron;

use Straitv\DynamicProduct\Helper\Data;
use Straitv\DynamicProduct\Logger\Logger;

class Run
{

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Run constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper,
        Logger $logger
    )
    {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Run the xml order process.
     * @return $this
     */
    public function execute()
    {
        $this->logMessage("Dynamic Product Created/Updated Cronjob Executed Time: " . date("h:i:sa"));
        
        $this->helper->run();
        return $this;
    }

    public function logMessage($message){
        return $this->logger->info($message);
    }
}
