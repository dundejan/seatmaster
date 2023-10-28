<?php

namespace App\Validator;

use App\Entity\Assignment;
use App\Entity\RepeatedAssignment;
use App\Repository\AssignmentRepository;
use App\Repository\RepeatedAssignmentRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Exception\LogicException;
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

	/**
	 * @throws Exception
	 */
	public function validate(mixed $value, Constraint $constraint): bool
	{
		$valid = true;

		/** @var AssignmentRepository $assignmentRepository */
		$assignmentRepository = $this->em->getRepository(Assignment::class);
		/** @var RepeatedAssignmentRepository $repeatedAssignmentRepository */
		$repeatedAssignmentRepository = $this->em->getRepository(RepeatedAssignment::class);

		$personConflictsWithAssignments = $assignmentRepository->findOverlappingAssignments($value, "person");
		$seatConflictsWithAssignments = $assignmentRepository->findOverlappingAssignments($value, "seat");
		$personConflictsWithRepeatedAssignments = $repeatedAssignmentRepository->findOverlappingRepeatedAssignments($value, "person");
		$seatConflictsWithRepeatedAssignments = $repeatedAssignmentRepository->findOverlappingRepeatedAssignments($value, "seat");

		// Overlapping one-time assignments using same person
		if (count($personConflictsWithAssignments) > 0) {
			/** @phpstan-ignore-next-line */
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ numberOfCollisions }}', strval(count($personConflictsWithAssignments)))
				->setParameter('{{ collisionType }}', 'person')
				->setParameter('{{ collidingAssignment }}',
					'id: ' . $personConflictsWithAssignments[0]['id'] .
					', from: ' . TimeHelper::getDateTimeAsString($personConflictsWithAssignments[0]['fromDate']) . ' UTC' .
					', to: ' . TimeHelper::getDateTimeAsString($personConflictsWithAssignments[0]['toDate']) . ' UTC'
					)
				->setParameter('{{ assignmentType }}', 'one-time')
				->addViolation();

			$valid = false;
		}

		// Overlapping one-time assignments using same seat
		if (count($seatConflictsWithAssignments) > 0) {
			/** @phpstan-ignore-next-line */
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ numberOfCollisions }}', strval(count($seatConflictsWithAssignments)))
				->setParameter('{{ collisionType }}', 'seat')
				->setParameter('{{ collidingAssignment }}',
					'id: ' . $seatConflictsWithAssignments[0]['id'] .
					', from: ' . TimeHelper::getDateTimeAsString($seatConflictsWithAssignments[0]['fromDate']) . ' UTC' .
					', to: ' . TimeHelper::getDateTimeAsString($seatConflictsWithAssignments[0]['toDate']) . ' UTC'
					)
				->setParameter('{{ assignmentType }}', 'one-time')
				->addViolation();

			$valid = false;
		}

		// Overlapping repeated assignments using same person
		if (count($personConflictsWithRepeatedAssignments) > 0) {
			$dayString = date(
				'l',
				strtotime("Sunday + {$personConflictsWithRepeatedAssignments[0]['dayOfWeek']} days") ?:
					throw new LogicException('strtotime() could not create dateTime'));

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

			$valid = false;
		}

		// Overlapping repeated assignments using same seat
		if (count($seatConflictsWithRepeatedAssignments) > 0) {
			$dayString = date(
				'l',
				strtotime("Sunday + {$seatConflictsWithRepeatedAssignments[0]['dayOfWeek']} days") ?:
					throw new LogicException('strtotime() could not create dateTime'));

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

			$valid = false;
		}

		return $valid;
	}
}
