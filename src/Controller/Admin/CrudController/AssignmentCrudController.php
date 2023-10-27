<?php

namespace App\Controller\Admin\CrudController;

use App\Entity\Assignment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class AssignmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Assignment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
	            ->hideOnForm(),
            DateTimeField::new('fromDate')
	            ->setLabel('From')
	            ->setFormTypeOption('view_timezone', 'Europe/Prague'),
	        DateTimeField::new('toDate')
		        ->setLabel('To')
	            ->setFormTypeOption('view_timezone', 'Europe/Prague'),
	        AssociationField::new('seat')
		        ->autocomplete()
		        ->setRequired(true),
	        AssociationField::new('person')
		        ->autocomplete()
		        ->setRequired(true),
        ];
    }

	public function configureCrud(Crud $crud): Crud
	{
		return $crud
			->setEntityLabelInSingular('One-time assignment')
			->setEntityLabelInPlural('One-time assignments')
			->setTimezone('Europe/Prague')
			;
	}

	/**
	 * @param EntityManagerInterface $entityManager
	 * @param Assignment $entityInstance
	 * @return void
	 */
	public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
	{
		$user = $this->getUser();
		if (!$user instanceof User) {
			throw new \LogicException('Currently logged user is not user.');
		}

		parent::updateEntity($entityManager, $entityInstance);
	}

	public function configureFilters(Filters $filters): Filters
	{
		return parent::configureFilters($filters)
			->add('fromDate')
			->add('toDate')
			->add('seat')
			->add('person');
	}
}
