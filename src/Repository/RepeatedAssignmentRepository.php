<?php

namespace App\Repository;

use App\Entity\Office;
use App\Entity\RepeatedAssignment;
use App\Entity\Seat;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

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
