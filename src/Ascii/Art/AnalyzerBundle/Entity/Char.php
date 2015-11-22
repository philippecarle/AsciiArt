<?php

namespace Ascii\Art\AnalyzerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Char
 *
 * @ORM\Table(name="chars")
 * @ORM\Entity(repositoryClass="Ascii\Art\AnalyzerBundle\Repository\CharsRepository")
 */
class Char
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ascii_char", type="string", length=1)
     */
    private $asciiChar;

    /**
     * @var integer
     *
     * @ORM\Column(name="luminosity", type="integer")
     */
    private $luminosity;

	/**
	 * Char constructor.
	 * @param string $asciiChar
	 * @param int $luminosity
	 */
	public function __construct($asciiChar, $luminosity)
	{
		$this->asciiChar = $asciiChar;
		$this->luminosity = $luminosity;
	}


	/**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set asciiChar
     *
     * @param string $asciiChar
     *
     * @return Char
     */
    public function setAsciiChar($asciiChar)
    {
        $this->asciiChar = $asciiChar;

        return $this;
    }

    /**
     * Get asciiChar
     *
     * @return string
     */
    public function getAsciiChar()
    {
        return $this->asciiChar;
    }

    /**
     * Set luminosity
     *
     * @param integer $luminosity
     *
     * @return Char
     */
    public function setLuminosity($luminosity)
    {
        $this->luminosity = $luminosity;

        return $this;
    }

    /**
     * Get luminosity
     *
     * @return integer
     */
    public function getLuminosity()
    {
        return $this->luminosity;
    }
}

