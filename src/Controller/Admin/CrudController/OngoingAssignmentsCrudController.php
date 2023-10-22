<?php

namespace App\Controller\Admin\CrudController;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Exception;

class OngoingAssignmentsCrudController extends AssignmentCrudController
{
	public function configureCrud(Crud $crud): Crud
	{
		return parent::configureCrud($crud)
			->setPageTitle(Crud::PAGE_INDEX, 'Currently ongoing one-time assignments');
	}

	/**
	 * @throws Exception
	 */
	public function createIndexQueryBuilder(
		SearchDto $searchDto,
		EntityDto $entityDto,
		FieldCollection $fields,
		FilterCollection $filters
	): QueryBuilder {
		$now = new DateTime('now', new DateTimeZone('UTC'));

		return parent::createIndexQueryBuilder(
			$searchDto,
			$entityDto,
			$fields,
			$filters
		)
			->andWhere('entity.fromDate <= :currentDate')
			->andWhere('entity.toDate > :currentDate')
			->setParameter('currentDate', $now)
			;
	}
}