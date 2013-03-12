<?php

namespace Namshi\VoyeurBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Finder\Finder;
use Imagick;

class VoyeurDiffCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('before', InputArgument::REQUIRED, 'The directory with the former images'),
                new InputArgument('after', InputArgument::REQUIRED, 'The directory with the latter images'),
                new InputOption('diff-dir', '', InputOption::VALUE_OPTIONAL, 'The directory where diffs will be saved'),
            ))
            ->setDescription('Generate diffs between images in 2 directories')
            ->setHelp(<<<EOT
The <info>namshi:voyeur:diff</info> command is useful to generate diffs of images stored in 2 separate directories.
EOT
            )
            ->setName('namshi:voyeur:diff')
            ->setAliases(array('namshi:voyeur:diff'))
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $output->writeln(sprintf('<bg=green;options=bold>Voyeur is going to compare screenshots / images...</bg=green;options=bold>'));
        $before     = $input->getArgument('before');
        $after      = $input->getArgument('after');
        $output->writeln(sprintf('<info>Analyzing the directory %s</info>', $before));
        $finder     = new Finder();
        $diffDir    = $input->getOption('diff-dir') ?: $after . DIRECTORY_SEPARATOR . 'diff';
        
        foreach ($finder->files()->in($before) as $file) {
            $output->writeln(sprintf('<info>Found file %s</info>', $file->getFilename()));
            $this->processFileToBeCompared($file, $before, $after, $diffDir, $output);
        }

        $output->writeln(sprintf('<info>Diff generated in %s</info>', $diffDir));
    }
    
    /**
     * Checks for a file named as $file in the $after folder: if it finds it,
     * a diff is generated into the $diffDir.
     *
     * @param SplFileInfo $file
     * @param string $before
     * @param string $after
     * @param string $diffDir
     * @param OutputInterface $output
     */
    protected function processFileToBeCompared(\SplFileInfo $file, $before, $after, $diffDir, OutputInterface $output)
    {
        $filePath           = $before . DIRECTORY_SEPARATOR . $file->getFilename();
        $specularFilePath   = $after . DIRECTORY_SEPARATOR . $file->getFilename();

        if (file_exists($specularFilePath)) {
            $output->writeln(sprintf('<info>Found specular file in %s</info>', $after));
            $diff = $this->compareFiles($filePath, $specularFilePath);
            
            if (!is_dir($diffDir)) {
                mkdir($diffDir, 0777, true);
            }


            file_put_contents($diffDir . DIRECTORY_SEPARATOR . $file->getFilename(), $diff);
            $output->writeln(sprintf('<info>Generated diff of %s</info>', $file->getFilename()));
        } else {
            $output->writeln(sprintf('<comment>No specular file was found in %s, ignoring %s</comment>', $after, $file->getFilename()));
        }
    }
    
    /**
     * Generates a diff between two files.
     * 
     * @param string $filePath
     * @param string $specularFilePath
     * @return base64encoded image
     */
    protected function compareFiles($filePath, $specularFilePath)
    {
        $imageBefore    = new Imagick($filePath);
        $imageAfter     = new Imagick($specularFilePath);
        $result         = $imageBefore->compareImages($imageAfter, Imagick::METRIC_UNDEFINED);
        $result[0]->setImageFormat("png");
        
        return $result[0];
    }
}