<?php

namespace App\Validator;

use DateTime;
use DateTimeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsFutureAssignmentValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        assert($constraint instanceof IsFutureAssignment);

		assert($value instanceof DateTimeInterface);

	    $currentDateTime = new DateTime();

		if ($value < $currentDateTime) {
			// The date is in the past.
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ value }}', $value->format('Y-m-d H:i:s'))
				->addViolation();
		}
    }
}
