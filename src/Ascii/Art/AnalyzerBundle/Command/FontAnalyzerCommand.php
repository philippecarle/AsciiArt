<?php

namespace Ascii\Art\AnalyzerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FontAnalyzerCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('font:analyze')
            ->setDescription('Analyze all characters and store them with associated grey value');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $chars = [];
        for($i=33; $i<126; $i++) {
            $chars[] = chr((string)$i);
        }
        var_dump($chars);
    }
}
