<?php

namespace App\Validator;

use App\Entity\Assignment;
use App\Entity\RepeatedAssignment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsAvailableAssignmentValidator extends ConstraintValidator
{
	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	public function validate(mixed $value, Constraint $constraint): void
	{
		$personConflicts = array();
		$seatConflicts = array();

		if ($value instanceof Assignment) {
			/** @phpstan-ignore-next-line */
			$personConflicts = $this->em
				->getRepository(Assignment::class)
				->findOverlappingWithRangeForPerson($value);
			/** @phpstan-ignore-next-line */
			$seatConflicts = $this->em
				->getRepository(Assignment::class)
				->findOverlappingWithRangeForSeat($value);
		}

		if ($value instanceof RepeatedAssignment) {
			/** @phpstan-ignore-next-line */
			$personConflicts = $this->em
				->getRepository(RepeatedAssignment::class)
				->findOverlappingWithRangeForPerson($value);
			/** @phpstan-ignore-next-line */
			$seatConflicts = $this->em
				->getRepository(RepeatedAssignment::class)
				->findOverlappingWithRangeForSeat($value);
		}

		if (count($personConflicts) > 0) {
			/** @phpstan-ignore-next-line */
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ value }}', 'person')
				->addViolation();
		}

		if (count($seatConflicts) > 0) {
			/** @phpstan-ignore-next-line */
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ value }}', 'seat')
				->addViolation();
		}
	}
}
