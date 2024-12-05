<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CapistranoFile
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity
 */
#[ORM\Table(name: 'capistrano_symlink')]
#[ORM\Entity]
class CapistranoSymlink
{

    use IdentifiableTrait;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string')]
    protected $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'source_location', type: 'string')]
    protected $sourceLocation;

    /**
     * @var string
     */
    #[ORM\Column(name: 'destination_location', type: 'string')]
    protected $destinationLocation;

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
    public function getSourceLocation()
    {
        return $this->sourceLocation;
    }

    /**
     * @param string $sourceLocation
     */
    public function setSourceLocation(string $sourceLocation)
    {
        $this->sourceLocation = $sourceLocation;
    }

    /**
     * @return string
     */
    public function getDestinationLocation()
    {
        return $this->destinationLocation;
    }

    /**
     * @param string $destinationLocation
     */
    public function setDestinationLocation(string $destinationLocation)
    {
        $this->destinationLocation = $destinationLocation;
    }
}
