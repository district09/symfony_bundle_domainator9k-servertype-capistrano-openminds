<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Validator\Constraints as CapistranoAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CapistranoFile
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="capistrano_file")
 */
class CapistranoFile
{

    use IdentifiableTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(min="2", max="255")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     */
    protected $filename;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     */
    protected $extension;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $location;

    /**
     * @var int
     *
     * @ORM\Column(name="chmod",type="smallint",options={"unsigned":true, "default":644})
     * @Assert\NotBlank()
     * @CapistranoAssert\ChmodMode()
     */
    protected $chmod;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $content;

    /**
     * @var CapistranoFile
     *
     * @ORM\ManyToOne(targetEntity="CapistranoFile")
     */
    protected $originalCapistranoFile;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(?string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension(string $extension)
    {
        $this->extension = $extension;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location)
    {
        $this->location = $location;
    }

    /**
     * @return int
     */
    public function getChmod()
    {
        return $this->chmod;
    }

    /**
     * @param int $chmod
     */
    public function setChmod($chmod)
    {
        $this->chmod = (int) $chmod;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function __clone()
    {
        $this->id = null;
    }

    /**
     * @return CapistranoFile
     */
    public function getOriginalCapistranoFile()
    {
        return $this->originalCapistranoFile;
    }

    /**
     * @param CapistranoFile $originalCapistranoFile
     */
    public function setOriginalCapistranoFile(CapistranoFile $originalCapistranoFile)
    {
        $this->originalCapistranoFile = $originalCapistranoFile;
    }
}
