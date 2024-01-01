<?php

namespace App\Repository;

use App\Entity\Assignment;
use App\Entity\Office;
use App\Entity\Person;
use App\Entity\RepeatedAssignment;
use App\Entity\Seat;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
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
	 * @throws Exception
	 */
	public function findCurrentlyOngoing(mixed $parameter = null): mixed
	{
		$now = new DateTime('now', new DateTimeZone('UTC'));

		return $this->findOngoing($now, $now, $parameter);
	}

	/**
	 * @throws Exception
	 */
	public function findOngoing(DateTime $from, DateTime $to, mixed $parameter = null): mixed
	{
		$qb = $this->createQueryBuilder('e');

		// Basic time condition for assignments ongoing between from and to
		$qb->andWhere('e.fromDate <= :toDate AND e.toDate > :fromDate')
			->setParameter('fromDate', $from)
			->setParameter('toDate', $to);

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
			// If parameter is person instance, add additional condition
			else if ($parameter instanceof Person) {
				$qb->andWhere('e.person = :person')
					->setParameter('person', $parameter);
			}
		}

		// Execute and return the query result
		return $qb->getQuery()->execute();
	}

	/**
	 * @param Assignment|RepeatedAssignment $assignment New Assignment or RepeatedAssignment that needs to be validated
	 * @param string $param Parameter to validate the new assignment against "person" or "seat"
	 * @return mixed Overlapping assignments
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function findOverlappingAssignments(Assignment|RepeatedAssignment $assignment, string $param) : mixed
	{
		// Ensure that $param is one of the allowed values.
		if (!in_array($param, ['person', 'seat'], true)) {
			throw new InvalidArgumentException("Invalid field: $param");
		}

		$qb = $this->createQueryBuilder('e');

		// Find overlapping assignments with new one-time Assignment
		if ($assignment instanceof Assignment) {
			// Time overlapping assignment
			$qb->andWhere('e.fromDate < :toDate AND e.toDate > :fromDate AND e.id <> :id')
				->setParameter('fromDate', $assignment->getFromDate())
				->setParameter('toDate', $assignment->getToDate())
				->setParameter('id', $assignment->getId() ?: -1)
			;

			// Filter just those with the same person
			if ($param === 'person') {
				$qb->andWhere('e.person = :person')
					->setParameter('person', $assignment->getPerson());
			}
			// Filter just those with the same seat
			else {
				$qb->andWhere('e.seat = :seat')
					->setParameter('seat', $assignment->getSeat());
			}

			return $qb->getQuery()->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)->execute();
		}
		// Find overlapping assignments with new RepeatedAssignment
		else {
			$em = $this->getEntityManager();
			$connection = $em->getConnection();

			$dayOfWeek = $assignment->getDayOfWeek();
			$fromTime = $assignment->getFromTime();
			$toTime = $assignment->getToTime();
			$person = $assignment->getPerson();
			$seat = $assignment->getSeat();
			/** @var DateTime $startDate */
			$startDate = $assignment->getStartDate();
			/** @var null|DateTime $untilDate */
			$untilDate = $assignment->getUntilDate();

			if ($fromTime === null || $toTime === null || $person === null || $seat === null) {
				throw new LogicException('fromTime or toTime or Person or Seat null, but never should be');
			}

			$param_id = $param . '_id';

			$sql = "
            SELECT *,
                   e.from_date AS \"fromDate\",
                   e.to_date AS \"toDate\"
            FROM assignment e 
            WHERE e.$param_id = :param_id 
            AND (EXTRACT(dow FROM e.from_date) = :dayOfWeek OR EXTRACT(dow FROM e.to_date) = :dayOfWeek)
            AND (
                ((e.from_date::timestamptz AT TIME ZONE 'Europe/Prague')::TIME < :toTime 
                     AND 
                 (e.to_date::timestamptz AT TIME ZONE 'Europe/Prague')::TIME > :fromTime)
            )
            AND (
                e.from_date >= :startDate
            )
       		";

			$params = [
				// As param_id return personId or seatId based on $param value
				'param_id' => $param === 'person' ? $person->getId() : $seat->getId(),
				'dayOfWeek' => $dayOfWeek !== 7 ? $dayOfWeek : 0, // Adjust for PostgreSQL day of week
				'fromTime' => $fromTime->format('H:i:s'),
				'toTime' => $toTime->format('H:i:s'),
				'startDate' => $startDate->format('Y-m-d H:i:s'),
			];

			if ($untilDate) {
				$sql .= "
			AND (
				DATE(e.to_date) <= DATE(:untilDate)
			)";
				$params['untilDate'] = $untilDate->format('Y-m-d');
			}

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
