<?php

namespace App\Repository;

use App\Entity\Assignment;
use App\Entity\Person;
use App\Entity\Seat;
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

	public function findOverlappingWithRangeForPerson(\DateTime $startDate, \DateTime $endDate, Person $person, ?int $id) : mixed
	{
		$qb = $this->createQueryBuilder('e');

		return $qb->andWhere('e.person = :person')
			->setParameter('person', $person)
			->andWhere('e.fromDate < :toDate AND e.toDate > :fromDate AND e.id <> :id')
			->setParameter('fromDate', $startDate)
			->setParameter('toDate', $endDate)
			->setParameter('id', $id)
			->getQuery()
			->execute()
			;
	}

	public function findOverlappingWithRangeForSeat(\DateTime $startDate, \DateTime $endDate, Seat $seat, ?int $id) : mixed
	{
		$qb = $this->createQueryBuilder('e');

		return $qb->andWhere('e.seat = :seat')
			->setParameter('seat', $seat)
			->andWhere('e.fromDate < :toDate AND e.toDate > :fromDate AND e.id <> :id')
			->setParameter('fromDate', $startDate)
			->setParameter('toDate', $endDate)
			->setParameter('id', $id)
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
