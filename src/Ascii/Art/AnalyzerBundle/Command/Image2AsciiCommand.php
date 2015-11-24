<?php

namespace Ascii\Art\AnalyzerBundle\Command;

use Intervention\Image\AbstractFont;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Image2AsciiCommand
 * @package Ascii\Art\AnalyzerBundle\Command
 */
class Image2AsciiCommand extends ContainerAwareCommand
{
	const CHAR_SIZE = 50;

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('ascii:image')
			->setDescription('Convert image to Ascii')
			->addOption(
				'size',
				'sz',
				InputOption::VALUE_OPTIONAL,
				'Size of image',
				500
			)
			->addOption(
				'output',
				'o',
				InputOption::VALUE_OPTIONAL,
				'Output : text or image',
				'text'
			)
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$newWidth = $input->getOption('size');

		$em = $this
			->getContainer()
			->get('doctrine.orm.entity_manager');

		$chars = $em->getRepository('AsciiArtAnalyzerBundle:Char')->getAllChars();

		$manager = new ImageManager(['driver' => 'imagick']);
		$image = $manager
			->make("files/goldengate.jpg");

		/**
		 * @var \Imagick $imagick
		 */
		$imagick = $image
			->resize($newWidth, $newWidth / $image->getWidth() * $image->getHeight())
			->getCore();

		list ($width, $height) = array_values($imagick->getImageGeometry());

		$ascii = [];
		foreach (range(1, $height) as $y) {
			$string = "";
			foreach (range(1, $width) as $x) {
				$rgb = $imagick->getImagePixelColor($x, $y)->getColor();
				$pixelLuminosity = intval(($rgb['r'] + $rgb['g'] + $rgb['b']) / 3);
				$string .= $chars[$pixelLuminosity];
			}
			array_push($ascii, $string);
		}

		if($input->getOption('output') == 'image') {
			$this->asciiToImage($ascii);
		} else {
			echo implode("\n", $ascii);
		}
	}

	private function asciiToImage(array $ascii)
	{
		$fontFile = $this
			->getContainer()
			->get('kernel')
			->locateResource('@AsciiArtAnalyzerBundle/Resources/fonts/courier_bold.ttf')
		;

		$fontSize = self::CHAR_SIZE * 1.2;

		$manager = new ImageManager(['driver' => 'imagick']);
		$im = new \Imagick();
		$im->newImage(self::CHAR_SIZE * count(str_split($ascii[0])), self::CHAR_SIZE * count($ascii), '#FFFFFF');
		$image = $manager->make($im);

		foreach($ascii as $index => $row) {
			foreach(str_split($row) as $pos => $char) {
				$image->text(
					$char,
					self::CHAR_SIZE * ($pos + 1) - self::CHAR_SIZE/2,
					self::CHAR_SIZE/2 + $index * self::CHAR_SIZE,
					function(AbstractFont $font) use ($fontFile, $fontSize) {
						$font->file($fontFile);
						$font->size($fontSize);
						$font->align('center');
					}
				);
			}
		}

		$image->save('files/ascii.jpg');
	}
}
