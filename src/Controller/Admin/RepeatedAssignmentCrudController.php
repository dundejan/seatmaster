<?php

namespace App\Controller\Admin;

use App\Entity\RepeatedAssignment;
use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class RepeatedAssignmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RepeatedAssignment::class;
    }

    public function configureFields(string $pageName): iterable
    {
		// Declare this field here to perform logic with the default value
	    $startDateField = DateField::new('startDate')
		    ->setLabel('Starting date of repetition');
		// On new page, set the start date field to the current date, otherwise not
	    if ($pageName === Crud::PAGE_NEW) {
		    $startDateField->setFormTypeOption('data', new \DateTime('now'));
	    }

	    return [
		    FormField::addTab("Assignment"),
		    IdField::new('id')
			    ->hideOnForm(),
		    ChoiceField::new('dayOfWeek')
			    ->setLabel('Day of week')
			    ->setChoices([
				    'Monday' => 1,
				    'Tuesday' => 2,
				    'Wednesday' => 3,
				    'Thursday' => 4,
				    'Friday' => 5,
				    'Saturday' => 6,
				    'Sunday' => 7,
			    ])
			    ->setRequired(true),
		    TimeField::new('fromTime')
		        ->setLabel('From time'),
		    TimeField::new('toTime')
			    ->setLabel('To time'),
		    $startDateField,
		    DateField::new('untilDate')
		        ->setLabel('End date of repetition')
			    ->setHelp('(leave blank to repeat forever)'),
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
			// the labels used to refer to this entity in titles, buttons, etc.
			->setEntityLabelInSingular('Repeated assignment')
			->setEntityLabelInPlural('Repeated assignments')

			// the Symfony Security permission needed to manage the entity
			// (none by default, so you can manage all instances of the entity)
			// ->setEntityPermission('ROLE_EDITOR')
			;
	}
}
