<?php

namespace App\Repository;

use App\Entity\Assignment;
use App\Entity\Office;
use App\Entity\Person;
use App\Entity\Seat;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

	public function findOverlappingWithRangeForPerson(DateTimeInterface $startDate, DateTimeInterface $endDate, Person $person, ?int $id) : mixed
	{
		$qb = $this->createQueryBuilder('e');

		return $qb->andWhere('e.person = :person')
			->setParameter('person', $person)
			->andWhere('e.fromDate < :toDate AND e.toDate > :fromDate AND e.id <> :id')
			->setParameter('fromDate', $startDate)
			->setParameter('toDate', $endDate)
			->setParameter('id', $id ? $id : -1)
			->getQuery()
			->execute()
			;
	}

	public function findOverlappingWithRangeForSeat(DateTimeInterface $startDate, DateTimeInterface $endDate, Seat $seat, ?int $id) : mixed
	{
		$qb = $this->createQueryBuilder('e');

		return $qb->andWhere('e.seat = :seat')
			->setParameter('seat', $seat)
			->andWhere('e.fromDate < :toDate AND e.toDate > :fromDate AND e.id <> :id')
			->setParameter('fromDate', $startDate)
			->setParameter('toDate', $endDate)
			->setParameter('id', $id ? $id : -1)
			->getQuery()
			->execute()
			;
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
