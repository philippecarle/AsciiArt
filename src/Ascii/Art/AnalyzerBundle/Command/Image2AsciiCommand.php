<?php

namespace Ascii\Art\AnalyzerBundle\Command;

use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
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
			->setDescription('Convert image to Ascii')
			->addArgument(
				'size',
				InputArgument::OPTIONAL,
				'Width of resized image',
				500
			);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$ascii = "";
		$newWidth = $input->getArgument('size');

		$em = $this
			->getContainer()
			->get('doctrine.orm.entity_manager');

		$chars = $em->getRepository('AsciiArtAnalyzerBundle:Char')->getAllChars();

		$manager = new ImageManager(['driver' => 'imagick']);
		$image = $manager
			->make("files/image.jpg");

		/**
		 * @var \Imagick $imagick
		 */
		$imagick = $image
			->resize($newWidth, $newWidth / $image->getWidth() * $image->getHeight())
			->getCore();

		list ($width, $height) = array_values($imagick->getImageGeometry());

		foreach (range(1, $height) as $y) {
			foreach (range(1, $width) as $x) {
				$rgb = $imagick->getImagePixelColor($x, $y)->getColor();
				$pixelLuminosity = intval(($rgb['r'] + $rgb['g'] + $rgb['b']) / 3);
				$ascii .= $chars[$pixelLuminosity];
			}
			$ascii .= "\n";
		}

		echo $ascii;
	}
}
