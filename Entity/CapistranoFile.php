<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity;


use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CapistranoFile
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity
 *
 * @ORM\Entity()
 */
class CapistranoFile
{

    use IdentifiableTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name",type="string")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="filename",type="string")
     */
    protected $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="extension",type="string")
     */
    protected $extension;

    /**
     * @var string
     *
     * @ORM\Column(name="location",type="string")
     */
    protected $location;

    /**
     * @var string
     *
     * @ORM\Column(name="content",type="text")
     */
    protected $content;

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
    public function setFilename(string $filename)
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

    public function __clone() {
        $this->id = null;
    }
}