<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ChmodMode extends Constraint
{
    public $special = '0';

    public $user;

    public $group;

    public $other;

    public $message = '"{{ mode }}" is not a valid chmod mode.';

    public $patternMessage = '"{{ mode }}" Is not a valid chmod mode or does not match the required pattern "{{ pattern }}".';
}
