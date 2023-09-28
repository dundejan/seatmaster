<?php

namespace App\Controller\Admin;

use App\Entity\Assignment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
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
	        FormField::addTab("Assignment"),
            IdField::new('id')
	            ->hideOnForm(),
            DateTimeField::new('fromDate')
	            ->setLabel('From')
	            ->setFormTypeOption('view_timezone', 'Europe/Paris'),
	        DateTimeField::new('toDate')
		        ->setLabel('To')
	            ->setFormTypeOption('view_timezone', 'Europe/Paris'),
	        AssociationField::new('seat')
		        ->autocomplete()
		        ->setRequired(true),
	        AssociationField::new('person')
		        ->autocomplete()
		        ->setRequired(true),
	        FormField::addPanel("Repeated assignment"),
	        BooleanField::new('recurrence')
		        ->setLabel('Repeat each week')
	            ->renderAsSwitch(false),
	        DateField::new('repeatEndDate')
	            ->setLabel('Repeat each week until'),
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
}
