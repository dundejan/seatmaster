<?php

namespace App\Controller\Admin\CrudController;

use App\Entity\RepeatedAssignment;
use DateTime;
use DateTimeZone;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
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
		    $startDateField->setFormTypeOption('data', new DateTime('now', new DateTimeZone('UTC')));
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
		        ->setLabel('From time')
			    ->setFormTypeOption('view_timezone', 'UTC'),
		    TimeField::new('toTime')
			    ->setLabel('To time')
			    ->setFormTypeOption('view_timezone', 'UTC'),
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
			->setEntityLabelInSingular('Repeated assignment')
			->setEntityLabelInPlural('Repeated assignments')
			->setTimezone('UTC')
			;
	}

	public function configureFilters(Filters $filters): Filters
	{
		return parent::configureFilters($filters)
			->add('dayOfWeek')
			->add('seat')
			->add('person')
			->add('fromTime')
			->add('toTime')
			->add('startDate')
			->add('untilDate');
	}
}
