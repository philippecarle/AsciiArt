<?php

namespace Ascii\Art\AnalyzerBundle\Command;

use Ascii\Art\AnalyzerBundle\Entity\Char;
use Intervention\Image\AbstractFont;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Intervention\Image\ImageManager;

/**
 * Class FontAnalyzerCommand
 * @package Ascii\Art\AnalyzerBundle\Command
 */
class FontAnalyzerCommand extends ContainerAwareCommand
{
    const IMG_SIZE = 400;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ascii:font:analyze')
            ->setDescription('Analyze all characters and store them with associated grey value');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        $chars = [];
        for ($i = 33; $i < 126; $i++) {
            $chars[] = chr((string)$i);
        }

        $datas = [];

        $kernel = $this->getContainer()->get('kernel');
        $fontFile = $kernel->locateResource('@AsciiArtAnalyzerBundle/Resources/fonts/courier.ttf');
        $fontSize = self::IMG_SIZE / 2 * 1.25;
        foreach ($chars as $char) {
            $manager = new ImageManager(['driver' => 'imagick']);
            $im = new \Imagick();
            $im->newImage(self::IMG_SIZE, self::IMG_SIZE, '#FFFFFF');
            $image = $manager->make($im);
            $image->text($char, self::IMG_SIZE / 2, self::IMG_SIZE - $fontSize / 2, function (AbstractFont $font) use ($fontFile, $fontSize) {
                $font->file($fontFile);
                $font->size($fontSize);
                $font->align('center');
            });

            $datas[$char] = $this->getAverageLuminosity($image->getCore());
        }

        $min = min(array_values($datas));
        $max = max(array_values($datas));

        $values = [];
        foreach ($datas as $char => $value) {
            $values[intval($this->scaleValue($value, $min, $max))] = $char;
        }

        ksort($values);

        $finalValues = [];
        foreach (range(0, 255) as $lum) {
            if (!array_key_exists($lum, $values)) {
                $finalValues[$lum] = $this->getClosest($lum, $values);
            } else {
                $finalValues[$lum] = $values[$lum];
            }
        }


        foreach ($finalValues as $lum => $char) {
            $obj = new Char($char, $lum);
            $em->persist($obj);
        }

        $em->flush();

    }

    /**
     * @param \Imagick $imagick
     * @return float
     */
    private function getAverageLuminosity(\Imagick $imagick)
    {
        $colors = 0;
        foreach (range(1, self::IMG_SIZE) as $x) {
            foreach (range(1, self::IMG_SIZE) as $y) {
                $rgb = $imagick->getImagePixelColor($x, $y)->getColor();
                $colors += ($rgb['r'] + $rgb['g'] + $rgb['b']) / 3;
            }
        }
        return $colors / (self::IMG_SIZE ** 2);
    }

    /**
     * @param $value
     * @param $min
     * @param $max
     * @return float
     */
    private function scaleValue($value, $min, $max)
    {
        return ((($value - $min) * (255 - 0)) / ($max - $min)) + 0;
    }

    /**
     * @param $search
     * @param array $array
     * @return string
     */
    private function getClosest($search, array $array)
    {
        $distances = [];
        $keys = array_keys($array);

        foreach ($keys as $key => $num) {
            $distances[$key] = abs($search - $num);
        }

        return $array[$keys[array_search(min($distances), $distances)]];
    }
}
