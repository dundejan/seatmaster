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
		$personConflictsWithAssignments = array();
		$seatConflictsWithAssignments = array();
		$personConflictsWithRepeatedAssignments = array();
		$seatConflictsWithRepeatedAssignments = array();

		$assignmentRepository = $this->em->getRepository(Assignment::class);
		$repeatedAssignmentRepository = $this->em->getRepository(RepeatedAssignment::class);

		/** @phpstan-ignore-next-line */
		$personConflictsWithAssignments = $assignmentRepository->findOverlappingAssignments($value, "person");
		/** @phpstan-ignore-next-line */
		$seatConflictsWithAssignments = $assignmentRepository->findOverlappingAssignments($value, "seat");
		/** @phpstan-ignore-next-line */
		$personConflictsWithRepeatedAssignments = $repeatedAssignmentRepository->findOverlappingRepeatedAssignments($value, "person");
		/** @phpstan-ignore-next-line */
		$seatConflictsWithRepeatedAssignments = $repeatedAssignmentRepository->findOverlappingRepeatedAssignments($value, "seat");

		if (count($personConflictsWithAssignments) > 0) {
			/** @phpstan-ignore-next-line */
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ param }}', 'person')
				->setParameter('{{ assignmentType }}', 'one-time')
				->addViolation();
		}

		if (count($seatConflictsWithAssignments) > 0) {
			/** @phpstan-ignore-next-line */
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ param }}', 'seat')
				->setParameter('{{ assignmentType }}', 'one-time')
				->addViolation();
		}

		if (count($personConflictsWithRepeatedAssignments) > 0) {
			/** @phpstan-ignore-next-line */
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ param }}', 'person')
				->setParameter('{{ assignmentType }}', 'repeated')
				->addViolation();
		}

		if (count($seatConflictsWithRepeatedAssignments) > 0) {
			/** @phpstan-ignore-next-line */
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ param }}', 'seat')
				->setParameter('{{ assignmentType }}', 'repeated')
				->addViolation();
		}
	}
}
