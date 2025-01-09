<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ChmodMode extends Constraint
{

    public function __construct(
        public $special = '0',
        public $user = null,
        public $group = null,
        public $other = null,
        public $message = '"{{ mode }}" is not a valid chmod mode.',
        public $patternMessage = '"{{ mode }}" Is not a valid chmod mode or does not match the required pattern "{{ pattern }}".',
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct($options, $groups, $payload);
    }

}
