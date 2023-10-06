<?php

namespace App\Repository;

use App\Entity\Assignment;
use App\Entity\Office;
use App\Entity\RepeatedAssignment;
use App\Entity\Seat;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Symfony\Component\Form\Exception\LogicException;

/**
 * @extends ServiceEntityRepository<Assignment>
 *
 * @method Assignment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Assignment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Assignment[]    findAll()
 * @method Assignment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assignment::class);
    }

	/**
	 * @throws \Exception
	 */
	public function findCurrentlyOngoing(mixed $parameter = null): mixed
	{
		$qb = $this->createQueryBuilder('e');

		// Basic condition for ongoing assignments
		$qb->andWhere('e.fromDate <= :currentDate AND e.toDate >= :currentDate')
			// DateTimeZone here is set to UTC, because in database dates are also with utc time zone
			->setParameter('currentDate', new DateTime('now', new DateTimeZone('UTC')));

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

	public function findOverlappingAssignments(Assignment|RepeatedAssignment $assignment, string $param) : mixed
	{
		// Ensure that $param is one of the allowed values.
		if (!in_array($param, ['person', 'seat'], true)) {
			throw new InvalidArgumentException("Invalid field: $param");
		}

		$qb = $this->createQueryBuilder('e');

		if ($assignment instanceof Assignment) {
			// Time overlapping assignment
			$qb->andWhere('e.fromDate < :toDate AND e.toDate > :fromDate AND e.id <> :id')
				->setParameter('fromDate', $assignment->getFromDate())
				->setParameter('toDate', $assignment->getToDate())
				->setParameter('id', $assignment->getId() ?: -1)
			;

			// Filter just those for the same person
			if ($param === 'person') {
				$qb->andWhere('e.person = :person')
					->setParameter('person', $assignment->getPerson());
			}
			// Filter just those for the same seat
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

			$dayOfWeek = $assignment->getDayOfWeek();
			$fromTime = $assignment->getFromTime();
			$toTime = $assignment->getToTime();
			$person = $assignment->getPerson();
			$seat = $assignment->getSeat();

			if ($fromTime === null || $toTime === null || $person === null || $seat === null) {
				throw new LogicException('fromTime or toTime or Person or Seat null, but never should be');
			}

			$adjustedFromTime = clone $fromTime;
			$adjustedToTime = clone $toTime;
			$adjustedFromTime->modify('-2 hours');
			$adjustedToTime->modify('-2 hours');

			$paramId = $param . '_id';

			$sql = "
            SELECT * FROM assignment e 
            WHERE e.$paramId = :paramId 
            AND (EXTRACT(dow FROM e.from_date) = :dayOfWeek OR EXTRACT(dow FROM e.to_date) = :dayOfWeek)
            AND (
                (e.from_date::TIME <= :toTime AND e.to_date::TIME >= :fromTime)
                OR
                (e.to_date::TIME >= :fromTime AND e.from_date::TIME <= :toTime)
            )
       		";

			$params = [
				// As paramId return personId or seatId based on $param value
				'paramId' => $param === 'person' ? $person->getId() : $seat->getId(),
				'dayOfWeek' => $dayOfWeek !== 7 ? $dayOfWeek : 0, // Adjust for PostgreSQL day of week
				'fromTime' => $adjustedFromTime->format('H:i:s'),
				'toTime' => $adjustedToTime->format('H:i:s'),
			];

			$stmt = $connection->prepare($sql);
			return $stmt->executeQuery($params)->fetchAllAssociative();
		}
	}

//    /**
//     * @return Assignment[] Returns an array of Assignment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Assignment
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
