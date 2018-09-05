<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validator for the chmod mode.
 */
class ChmodModeValidator extends ConstraintValidator
{

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_int($value) && (!is_string($value) || !ctype_digit($value))) {
            throw new UnexpectedTypeException($value, 'int');
        }

        $regex = '';
        $violationPattern = '';
        foreach (['special', 'user', 'group', 'other'] as $property) {
            if (null !== $constraint->{$property} && '' !== $constraint->{$property}) {
                $regex .= $constraint->{$property};
                $violationPattern .= $constraint->{$property};

                if ($property === 'special' && $constraint->special < 1) {
                    $regex .= '?';
                }
            }
            else {
                $regex .= '[0-7]';
                $violationPattern .= 'x';
            }
        }

        if (!preg_match('/^' . $regex . '$/', $value)) {
            $message = $constraint->message;
            if ($violationPattern !== 'xxxx') {
                $message = $constraint->patternMessage;
            }

            $this->context->buildViolation($message)
                ->setParameter('{{ pattern }}', $violationPattern)
                ->setParameter('{{ mode }}', $value)
                ->addViolation();
        }
    }

}
