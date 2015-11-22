<?php

namespace Ascii\Art\AnalyzerBundle\Command;

use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Image2AsciiCommand
 * @package Ascii\Art\AnalyzerBundle\Command
 */
class Image2AsciiCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('image:ascii')
            ->setDescription('Convert image to Ascii');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
	    $ascii = "";

	    $em = $this
		    ->getContainer()
		    ->get('doctrine.orm.entity_manager')
	    ;

	    $chars = $em->getRepository('AsciiArtAnalyzerBundle:Char')->getAllChars();

		$manager = new ImageManager(['driver' => 'imagick']);
	    $image = $manager
		    ->make("files/Clarinsmen.jpg")
	    ;

	    $imagick = $image
	        ->resize(500, 500 / $image->getWidth() * $image->getHeight())
		    ->getCore()
	    ;

	    $image->save("test.jpg");

	    /**
	     * @var \Imagick $image
	     */
	    list ($width, $height) = array_values($imagick->getImageGeometry());
	    foreach (range(1, $height) as $y) {
		    foreach (range(1, $width) as $x) {
			    $rgb = $imagick->getImagePixelColor($x, $y)->getColor();
			    $pixelLuminosity = ($rgb['r'] + $rgb['g'] + $rgb['b']) / 3;
			    $ascii.= $this->getClosest($pixelLuminosity, $chars);
		    }
		    $ascii.= "\n";
	    }

	    echo $ascii;
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

		foreach ($keys as $key => $num)
		{
			$distances[$key] = abs($search - $num );
		}

		return $array[$keys[array_search(min($distances ), $distances)]];
	}
}
