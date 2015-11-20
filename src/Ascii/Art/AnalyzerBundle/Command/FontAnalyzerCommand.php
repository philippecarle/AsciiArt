<?php

namespace Ascii\Art\AnalyzerBundle\Command;

use Intervention\Image\AbstractFont;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Intervention\Image\ImageManager;

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

        $kernel = $this->getContainer()->get('kernel');
        $fontFile = $kernel->locateResource('@AsciiArtAnalyzerBundle/Resources/fonts/courier.ttf');
        foreach ($chars as $char) {
            $manager = new ImageManager(['driver' => 'imagick']);
            $im = new \Imagick();
            $im->newImage(200,200,'#FFFFFF');
            $image = $manager->make($im);
            $image->fill('#FFFFFF');
            $image->text($char, 100, 190, function(AbstractFont $font) use ($fontFile) {
                $font->file($fontFile);
                $font->size(240);
                $font->align('center');
            });
            $image->save("./files/$char.jpg");

            $colors = 0;
            for($x=1; $x<= 200; $x++) {
                for($y=1; $y<= 200; $y++) {
                    $imagick = $image->getCore();
                    $rgb = $imagick->getImagePixelColor($x, $y)->getColor();
                    $colors+= ($rgb['r'] + $rgb['g'] + $rgb['b']) / 3;
                }
            }
            $averageColor = $colors / (200*200);
            $output->writeln("$char : $averageColor");


        }


    }
}
