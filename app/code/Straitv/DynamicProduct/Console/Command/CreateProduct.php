<?php
namespace Straitv\DynamicProduct\Console\Command;

use Straitv\DynamicProduct\Helper\Data;
use Magento\Framework\App\State;
use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateProduct extends Command
{
    /**
     * @var State
     */
    protected $appState;

    /**
     * Alphabroder constructor.
     * @param State $appState
     */
    public function __construct(
        State $appState
    ) {
        $this->appState = $appState;
        parent::__construct('straitv:create:product');
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('straitv:create:product');
        $this->setDescription('This is my console command.');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode('adminhtml');
        /**
         * @var $helper Data
         */
        $helper = ObjectManager::getInstance()->create(Data::class);
        if ($helper->run()) {
            $helper->logMessage("Product Created.");
            $output->writeln('Product Created.');
        } else {
            $output->writeln('Was not possible to run the command, please try again later. '.
            'Check if the extension is enabled on admin and if you enabled the plugin.');
        }
        return $this;
    }
}