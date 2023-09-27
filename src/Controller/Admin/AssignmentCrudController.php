<?php

namespace App\Controller\Admin;

use App\Entity\Assignment;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
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
	            ->setFormTypeOption('view_timezone', 'Europe/Paris'),
	        DateTimeField::new('toDate')
	            ->setFormTypeOption('view_timezone', 'Europe/Paris'),
	        AssociationField::new('seat')
		        ->autocomplete()
		        ->setRequired(true),
	        AssociationField::new('person')
		        ->autocomplete()
		        ->setRequired(true),
	        ChoiceField::new('recurrence')
		        ->setFormTypeOptions(['mapped' => false])
		        ->setLabel('Recurrence')
		        ->setChoices([
			        'One-time' => 'one_time',
			        'Weekly' => 'weekly'
		        ])
		        ->setRequired(true),
        ];
    }

	public function configureCrud(Crud $crud): Crud
	{
		return $crud
			// the labels used to refer to this entity in titles, buttons, etc.
			->setEntityLabelInSingular('Assignment')
			->setEntityLabelInPlural('Assignments')
			->setTimezone('Europe/Paris')

			// the Symfony Security permission needed to manage the entity
			// (none by default, so you can manage all instances of the entity)
			// ->setEntityPermission('ROLE_EDITOR')
			;
	}

	public function createEntity(string $entityFqcn): Assignment
	{
		// TODO: see https://symfony.com/bundles/EasyAdminBundle/current/crud.html#creating-persisting-and-deleting-entities
		$assignment = new Assignment();

		return $assignment;
	}

	public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
	{
		$this->persistEntity($entityManager, $entityInstance);
		$entityManager->flush();
	}

	public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
	{
		if ($entityInstance->getRecurrence() === 'weekly') {
			// Custom logic for weekly recurrence
		}

		$entityManager->persist($entityInstance);
		$entityManager->flush();
	}
}
