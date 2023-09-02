<?php

namespace App\Validator;

use App\Entity\Assignment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsAvailableAssignmentValidator extends ConstraintValidator
{
	private $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	public function validate($object, Constraint $constraint)
	{
		//TODO: Real validation

		$personConflicts = $this->em
			->getRepository(Assignment::class)
			->findOverlappingWithRangeForPerson($object->getFromDate(), $object->getToDate(), $object->getPerson())
		;
		$seatConflicts = $this->em
			->getRepository(Assignment::class)
			->findOverlappingWithRangeForSeat($object->getFromDate(), $object->getToDate(), $object->getSeat());

		if (count($personConflicts) > 0) {
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ value }}', 'person')
				->addViolation();
		}

		if (count($seatConflicts) > 0) {
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ value }}', 'seat')
				->addViolation();
		}
	}
}
