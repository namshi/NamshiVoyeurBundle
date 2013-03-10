<?php

namespace Namshi\VoyeurBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class VoyeurCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDescription('This command launches the Voyeur')
            ->setHelp(<<<EOT
The <info>namshi:voyeur</info> command launches a Voyeur, who will basically take screenshots of given URLs.
EOT
            )
            ->setName('namshi:voyeur')
            ->setAliases(array('namshi:voyeur'))
        ;
    }
    
    /**
     * Returns the configuration for voyeur.
     * 
     * @return \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag 
     */
    protected function getConfiguration()
    {
        return new ParameterBag($this->getContainer()->getParameter('namshi_voyeur'));
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $output->writeln(sprintf('<bg=green;options=bold>Voyeur is hungry for...</bg=green;options=bold>'));
        
        $time           = date('H') . date('i');
        $configuration  = $this->getConfiguration();
        
        foreach ($configuration->get('browsers') as $browser) {
            $output->writeln(sprintf('<fg=magenta>Boostrapping %s web browser</fg=magenta>', $browser));
            $webdriver = $this->getContainer()->get($browser);
            
            foreach ($configuration->get('urls') as $id => $url) {
                $this->saveScreenshot(
                    $webdriver, 
                    $url, 
                    $id, 
                    $configuration->get('base_url'),
                    $configuration->get('shots_dir'),
                    $browser,
                    $time,
                    $output        
                );
            }            
            
            $webdriver->stop();
            $output->writeln(sprintf('<info>Tests with %s are over</info>', $browser));
        }
    }
    
    /**
     * Saves the screenshot of the URL $baseUrl/$url, visiting it with the
     * $webdriver.
     * 
     * @param object $webdriver
     * @param string $url
     * @param string $baseUrl
     * @param string $dir
     * @param string $browser
     * @param string $time
     * @param OutputInterface $output 
     */
    protected function saveScreenshot($webdriver, $url, $id, $baseUrl, $dir, $browser, $time, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Visiting %s</info>', $url));
        $webdriver->visit($baseUrl . $url);
        $dir = $this->getScreenshotDirectory($dir, $browser, $time, $output);

        if (file_put_contents($dir . DIRECTORY_SEPARATOR . $id . ".png", $webdriver->getScreenshot())) {
            $output->writeln(sprintf('<info>Screenshot of %s taken</info>', $id));
        } else {
            $output->writeln(sprintf('<error>Error while giving a check to %s</error>', $id));
        }
    }
    
    /**
     * Returns the directory where the screenshot will be stored.
     * It will try to create it if it doesnt phisically exist.
     * 
     * @return string 
     */
    protected function getScreenshotDirectory($dir, $browser, $time, OutputInterface $output)
    {
        $filepath = $dir . DIRECTORY_SEPARATOR . $browser . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR .  date('m') . DIRECTORY_SEPARATOR . date('d') . DIRECTORY_SEPARATOR . $time;

        if (!is_dir($filepath)) {
            $output->writeln(sprintf('<info>Creating screenshot directory in %s</info>', $filepath));
            mkdir($filepath, 0777, true);
        }
        
        return $filepath;
    }
}