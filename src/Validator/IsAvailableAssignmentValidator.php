<?php

namespace App\Validator;

use App\Entity\Assignment;
use App\Entity\RepeatedAssignment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Helper\TimeHelper;

class IsAvailableAssignmentValidator extends ConstraintValidator
{
	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	public function validate(mixed $value, Constraint $constraint): void
	{
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
				->setParameter('{{ numberOfCollisions }}', strval(count($personConflictsWithAssignments)))
				->setParameter('{{ collisionType }}', 'person')
				->setParameter('{{ collidingAssignment }}',
					'id: ' . $personConflictsWithAssignments[0]['id'] .
					', from: ' . TimeHelper::getDateTimeAsString($personConflictsWithAssignments[0]['fromDate']) .
					', to: ' . TimeHelper::getDateTimeAsString($personConflictsWithAssignments[0]['toDate'])
					)
				->setParameter('{{ assignmentType }}', 'one-time')
				->addViolation();
		}

		if (count($seatConflictsWithAssignments) > 0) {
			/** @phpstan-ignore-next-line */
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ numberOfCollisions }}', strval(count($seatConflictsWithAssignments)))
				->setParameter('{{ collisionType }}', 'seat')
				->setParameter('{{ collidingAssignment }}',
					'id: ' . $seatConflictsWithAssignments[0]['id'] .
					', from: ' . TimeHelper::getDateTimeAsString($seatConflictsWithAssignments[0]['fromDate']) .
					', to: ' . TimeHelper::getDateTimeAsString($seatConflictsWithAssignments[0]['toDate'])
					)
				->setParameter('{{ assignmentType }}', 'one-time')
				->addViolation();
		}

		if (count($personConflictsWithRepeatedAssignments) > 0) {
			/** @phpstan-ignore-next-line */
			$dayString = date('l', strtotime("Sunday + {$personConflictsWithRepeatedAssignments[0]['dayOfWeek']} days"));

			/** @phpstan-ignore-next-line */
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ numberOfCollisions }}', strval(count($personConflictsWithRepeatedAssignments)))
				->setParameter('{{ collisionType }}', 'person')
				->setParameter('{{ collidingAssignment }}',
					'id: ' . $personConflictsWithRepeatedAssignments[0]['id'] .
					', ' . $dayString .
					', from: ' . TimeHelper::getDateTimeAsString($personConflictsWithRepeatedAssignments[0]['fromTime'], true) .
					', to: ' . TimeHelper::getDateTimeAsString($personConflictsWithRepeatedAssignments[0]['toTime'], true)
				)
				->setParameter('{{ assignmentType }}', 'repeated')
				->addViolation();
		}

		if (count($seatConflictsWithRepeatedAssignments) > 0) {
			/** @phpstan-ignore-next-line */
			$dayString = date('l', strtotime("Sunday + {$seatConflictsWithRepeatedAssignments[0]['dayOfWeek']} days"));

			/** @phpstan-ignore-next-line */
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ numberOfCollisions }}', strval(count($seatConflictsWithRepeatedAssignments)))
				->setParameter('{{ collisionType }}', 'seat')
				->setParameter('{{ collidingAssignment }}',
					'id: ' . $seatConflictsWithRepeatedAssignments[0]['id'] .
					', ' . $dayString .
					', from: ' . TimeHelper::getDateTimeAsString($seatConflictsWithRepeatedAssignments[0]['fromTime'], true) .
					', to: ' . TimeHelper::getDateTimeAsString($seatConflictsWithRepeatedAssignments[0]['toTime'], true)
				)
				->setParameter('{{ assignmentType }}', 'repeated')
				->addViolation();
		}
	}
}
