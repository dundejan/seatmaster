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
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
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
	public function getQueryForOngoing(DateTime $from, DateTime $to, mixed $parameter = null): QueryBuilder
	{
		// Create clones and convert time zones
		$fromParis = clone $from;
		$fromParis->setTimezone(new DateTimeZone('Europe/Paris'));
		$toParis = clone $to;
		$toParis->setTimezone(new DateTimeZone('Europe/Paris'));

		$qb = $this->createQueryBuilder('e');

		// 1 for monday, 2 for tuesday, 3 for wednesday, ...
		$currentDayOfWeek = $fromParis->format('N');

		// Basic condition for ongoing assignments
		$qb->andWhere('e.dayOfWeek = :currentDayOfWeek AND e.fromTime <= :toTime AND :fromTime < e.toTime' )
			->setParameter('currentDayOfWeek', $currentDayOfWeek)
			->setParameter('fromTime', $fromParis, Types::TIME_MUTABLE)
			->setParameter('toTime', $toParis, Types::TIME_MUTABLE);

		$qb->andWhere('e.startDate <= :fromTime AND COALESCE(e.untilDate, :infinityDate) >= :fromTime')
			->setParameter('fromTime', $fromParis)
			->setParameter('infinityDate', new DateTime('9999-12-31 23:59:59'));

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

		return $qb;
	}

	/**
	 * @throws \Exception
	 */
	public function getQueryForCurrentlyOngoing(mixed $parameter = null): QueryBuilder
	{
		$now = new DateTime('now', new DateTimeZone('UTC'));
		return $this->getQueryForOngoing($now, $now, $parameter);
	}

	/**
	 * @throws \Exception
	 */
	public function findCurrentlyOngoing(mixed $parameter = null): mixed
	{
		// Execute and return the query result
		return $this->getQueryForCurrentlyOngoing($parameter)->getQuery()->execute();
	}

	/**
	 * @throws \Exception
	 */
	public function findOngoing(DateTime $from, DateTime $to, mixed $parameter = null): mixed
	{
		// Execute and return the query result
		return $this->getQueryForOngoing($from, $to, $parameter)->getQuery()->execute();
	}

	/**
	 * @throws Exception
	 */
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
				->andWhere('e.startDate <= :untilDate AND COALESCE(e.untilDate, :infinityDate) >= :startDate')
				->setParameter('untilDate', $assignment->getUntilDate() ?: new DateTime('9999-12-31 23:59:59'))
				->setParameter('startDate', $assignment->getStartDate())
				->setParameter('infinityDate', new DateTime('9999-12-31 23:59:59'))
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

			return $qb->getQuery()->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)->execute();
		}
		else {
			$em = $this->getEntityManager();
			$connection = $em->getConnection();

			$fromDate = $assignment->getFromDate();
			$toDate = $assignment->getToDate();
			$person = $assignment->getPerson();
			$seat = $assignment->getSeat();

			if ($fromDate === null || $toDate === null || $person === null || $seat === null) {
				throw new LogicException('fromDate or toDate or Person or Seat null, but never should be');
			}

			/** @var DateTime $adjustedFromDate */
			$adjustedFromDate = clone $fromDate;
			/** @var DateTime $adjustedToDate */
			$adjustedToDate = clone $toDate;

			// Set timezone to modify the time +1 or +2 hours depending on the current DST
			$adjustedFromDate->setTimezone(new DateTimeZone('Europe/Prague'));
			$adjustedToDate->setTimezone(new DateTimeZone('Europe/Prague'));

			$param_id = $param . '_id';

			$sql = "
            SELECT *,
                   e.day_of_week AS \"dayOfWeek\", 
                   e.from_time AS \"fromTime\",
                   e.to_time AS \"toTime\"
            FROM repeated_assignment e 
            WHERE e.$param_id = :param_id 
            AND e.day_of_week = :dayOfWeek
            AND (
                (e.from_time::TIME < :toDate AND e.to_time::TIME > :fromDate)
                OR
                (e.to_time::TIME > :fromDate AND e.from_time::TIME < :toDate)
            )
            AND (
                e.start_date <= :startDate
            )
            AND (
 			   e.until_date IS NULL OR DATE(e.until_date) >= DATE(:untilDate)
			)
       		";

			$params = [
				// As param_id return personId or seatId based on $param value
				'param_id' => $param === 'person' ? $person->getId() : $seat->getId(),
				'dayOfWeek' => $adjustedFromDate->format('N'),
				'fromDate' => $adjustedFromDate->format('H:i:s'),
				'toDate' => $adjustedToDate->format('H:i:s'),
				'startDate' => $adjustedFromDate->format('Y-m-d'),
				'untilDate' => $adjustedToDate->format('Y-m-d'),
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
