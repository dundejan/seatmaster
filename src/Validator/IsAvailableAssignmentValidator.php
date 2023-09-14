<?php

namespace App\Validator;

use App\Entity\Assignment;
use App\Repository\AssignmentRepository;
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
		/** @phpstan-ignore-next-line */
		$personConflicts = $this->em
			->getRepository(Assignment::class)
			->findOverlappingWithRangeForPerson($value->getFromDate(), $value->getToDate(), $value->getPerson())
		;
		/** @phpstan-ignore-next-line */
		$seatConflicts = $this->em
			->getRepository(Assignment::class)
			->findOverlappingWithRangeForSeat($value->getFromDate(), $value->getToDate(), $value->getSeat());

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
