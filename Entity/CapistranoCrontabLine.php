<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CrontabLine
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity
 *
 * @ORM\Entity()
 */
class CapistranoCrontabLine
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
     * @ORM\Column(name="minute",type="string")
     */
    protected $minute;

    /**
     * @var string
     *
     * @ORM\Column(name="hour",type="string")
     */
    protected $hour;

    /**
     * @var string
     *
     * @ORM\Column(name="day_of_month",type="string")
     */
    protected $dayOfMonth;

    /**
     * @var string
     *
     * @ORM\Column(name="month_of_year",type="string")
     */
    protected $monthOfYear;

    /**
     * @var string
     *
     * @ORM\Column(name="day_of_week",type="string")
     */
    protected $dayOfWeek;

    /**
     * @var string
     *
     * @ORM\Column(name="command",type="string")
     */
    protected $command;

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name = null)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getMinute(): ?string
    {
        return $this->minute;
    }

    /**
     * @param string $minute
     */
    public function setMinute(string $minute = null)
    {
        $this->minute = $minute;
    }

    /**
     * @return string
     */
    public function getHour(): ?string
    {
        return $this->hour;
    }

    /**
     * @param string $hour
     */
    public function setHour(string $hour = null)
    {
        $this->hour = $hour;
    }

    /**
     * @return string
     */
    public function getDayOfMonth(): ?string
    {
        return $this->dayOfMonth;
    }

    /**
     * @param string $dayOfMonth
     */
    public function setDayOfMonth(string $dayOfMonth = null)
    {
        $this->dayOfMonth = $dayOfMonth;
    }

    /**
     * @return string
     */
    public function getMonthOfYear(): ?string
    {
        return $this->monthOfYear;
    }

    /**
     * @param string $monthOfYear
     */
    public function setMonthOfYear(string $monthOfYear = null)
    {
        $this->monthOfYear = $monthOfYear;
    }

    /**
     * @return string
     */
    public function getDayOfWeek(): ?string
    {
        return $this->dayOfWeek;
    }

    /**
     * @param string $dayOfWeek
     */
    public function setDayOfWeek(string $dayOfWeek = null)
    {
        $this->dayOfWeek = $dayOfWeek;
    }

    /**
     * @return string
     */
    public function getCommand(): ?string
    {
        return $this->command;
    }

    /**
     * @param string $command
     */
    public function setCommand(string $command = null)
    {
        $this->command = $command;
    }

    /**
     * @return string
     *  The complete cron string.
     */
    public function __toString()
    {
        return sprintf(
            '%s %s %s %s %s %s',
            $this->randomize($this->getMinute(), 59, 0),
            $this->randomize($this->getHour(), 23, 0),
            $this->randomize($this->getDayOfMonth(), 28),
            $this->randomize($this->getMonthOfYear(), 12),
            $this->randomize($this->getDayOfWeek(), 6),
            $this->getCommand()
        );
    }

    /**
     * Replace all "H" characters with a random value between $min and $max.
     *
     * @param string $value
     *  The string with "H" characters.
     * @param int $max
     *  The maximum replacement value.
     * @param int $min
     *  The minimum replacement value.
     *
     * @return string
     *  The randomized string.
     */
    private function randomize($string, $max, $min = 1)
    {
        return preg_replace_callback(
            '/H/',
            function ($match) use ($min, $max) {
                return mt_rand($min, $max);
            },
            $string
        );
    }
}
