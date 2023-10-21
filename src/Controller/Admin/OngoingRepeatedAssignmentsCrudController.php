<?php

namespace App\Controller\Admin;

use App\Repository\RepeatedAssignmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Exception;

class OngoingRepeatedAssignmentsCrudController extends RepeatedAssignmentCrudController
{
	private RepeatedAssignmentRepository $repeatedAssignmentRepository;

	public function __construct(RepeatedAssignmentRepository $repeatedAssignmentRepository)
	{
		$this->repeatedAssignmentRepository = $repeatedAssignmentRepository;
	}

	public function configureCrud(Crud $crud): Crud
	{
		return parent::configureCrud($crud)
			->setPageTitle(Crud::PAGE_INDEX, 'Currently ongoing repeated assignments');
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
		return $this->repeatedAssignmentRepository->getQueryForCurrentlyOngoing();
	}
}