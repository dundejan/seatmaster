<?php

namespace App\Repository;

use App\Entity\Assignment;
use App\Entity\Office;
use App\Entity\Person;
use App\Entity\RepeatedAssignment;
use App\Entity\Seat;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use http\Exception\InvalidArgumentException;
use Symfony\Component\Form\Exception\LogicException;

/**
 * @extends ServiceEntityRepository<RepeatedAssignment>
 *
 * @method RepeatedAssignment|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepeatedAssignment|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepeatedAssignment[]    findAll()
 * @method RepeatedAssignment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepeatedAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepeatedAssignment::class);
    }

	/**
	 * @throws \Exception
	 */
	public function findCurrentlyOngoing(mixed $parameter = null): mixed
	{
		$qb = $this->createQueryBuilder('e');

		// 1 for monday, 2 for tuesday, 3 for wednesday, ...
		$currentDayOfWeek = date('N');

		// Basic condition for ongoing assignments
		$qb->andWhere('e.dayOfWeek = :currentDayOfWeek AND e.fromTime <= :currentTime AND :currentTime < e.toTime' )
			->setParameter('currentDayOfWeek', $currentDayOfWeek)
			// DateTimeZone here is set to Europe/Paris, because time is stored in UTC, although it is meant to be in Europe/Paris
			->setParameter('currentTime', new DateTime('now', new DateTimeZone('Europe/Paris')), Types::TIME_MUTABLE);

		// If parameter is provided
		if ($parameter !== null) {
			// If parameter is office instance, add additional condition
			if ($parameter instanceof Office) {
				$qb->join('e.seat', 's')  // Join with Seat entity using alias 's'
				->join('s.office', 'o') // Join with Office entity using alias 'o'
				->andWhere('o = :office')
					->setParameter('office', $parameter);
			}
			// If parameter is seat instance, add additional condition
			else if ($parameter instanceof Seat) {
				$qb->andWhere('e.seat = :seat')
					->setParameter('seat', $parameter);
			}
		}

		// Execute and return the query result
		return $qb->getQuery()->execute();
	}

	public function findOverlappingRepeatedAssignments(Assignment|RepeatedAssignment $assignment, string $param) : mixed
	{
		// Ensure that $param is one of the allowed values.
		if (!in_array($param, ['person', 'seat'], true)) {
			throw new InvalidArgumentException("Invalid field: $param");
		}

		$qb = $this->createQueryBuilder('e');

		if ($assignment instanceof RepeatedAssignment) {
			$qb->andWhere('e.dayOfWeek = :dayOfWeek AND e.fromTime < :toTime AND e.toTime > :fromTime AND e.id <> :id')
				->setParameter('dayOfWeek', $assignment->getDayOfWeek())
				->setParameter('fromTime', $assignment->getFromTime())
				->setParameter('toTime', $assignment->getToTime())
				->setParameter('id', $assignment->getId() ?: -1)
				->andWhere('e.startDate < :untilDate AND COALESCE(e.untilDate, :infinityDate) > :startDate')
				->setParameter('untilDate', $assignment->getUntilDate() ?: new \DateTime('9999-12-31 23:59:59'))
				->setParameter('startDate', $assignment->getStartDate())
				->setParameter('infinityDate', new \DateTime('9999-12-31 23:59:59'))
			;

			// Filter just those for the same person
			if ($param === 'person') {
				$qb->andWhere('e.person = :person')
					->setParameter('person', $assignment->getPerson());
			} // Filter just those for the same seat
			else {
				$qb->andWhere('e.seat = :seat')
					->setParameter('seat', $assignment->getSeat());
			}

			return $qb->getQuery()->execute();
		}
		else {
			// TODO: test this, especially dealing with time zones, so the -2 modifying

			$em = $this->getEntityManager();
			$connection = $em->getConnection();

			$fromDate = $assignment->getFromDate();
			$toDate = $assignment->getToDate();
			$person = $assignment->getPerson();
			$seat = $assignment->getSeat();

			if ($fromDate === null || $toDate === null || $person === null || $seat === null) {
				throw new LogicException('fromDate or toDate or Person or Seat null, but never should be');
			}

			$adjustedFromDate = clone $fromDate;
			$adjustedToDate = clone $toDate;
			$adjustedFromDate->modify('+2 hours');
			$adjustedToDate->modify('+2 hours');

			$paramId = $param . '_id';

			$sql = "
            SELECT * FROM repeated_assignment e 
            WHERE e.$paramId = :paramId 
            AND e.day_of_week = :dayOfWeek
            AND (
                (e.from_time::TIME <= :toDate AND e.to_time::TIME >= :fromDate)
                OR
                (e.to_time::TIME >= :fromDate AND e.from_time::TIME <= :toDate)
            )
       		";

			$params = [
				// As paramId return personId or seatId based on $param value
				'paramId' => $param === 'person' ? $person->getId() : $seat->getId(),
				'dayOfWeek' => $adjustedFromDate->format('N'),
				'fromDate' => $adjustedFromDate->format('H:i:s'),
				'toDate' => $adjustedToDate->format('H:i:s'),
			];

			$stmt = $connection->prepare($sql);
			return $stmt->executeQuery($params)->fetchAllAssociative();
		}
	}

//    /**
//     * @return RepeatedAssignment[] Returns an array of RepeatedAssignment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RepeatedAssignment
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
