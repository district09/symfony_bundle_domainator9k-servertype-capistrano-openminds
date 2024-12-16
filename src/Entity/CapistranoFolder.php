<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Validator\Constraints as CapistranoAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CapistranoFile
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity
 */
#[ORM\Table(name: 'capistrano_folder')]
#[ORM\Entity]
class CapistranoFolder
{

    use IdentifiableTrait;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    protected $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'filename', type: 'string')]
    #[Assert\NotBlank]
    protected $location;

    /**
     * @var int
     *
     * @CapistranoAssert\ChmodMode(user="7")
     */
    #[ORM\Column(name: 'chmod', type: 'smallint', options: ['unsigned' => true, 'default' => 750])]
    #[Assert\NotBlank]
    protected $chmod = 750;

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

    public function __clone()
    {
        $this->id = null;
    }
}
