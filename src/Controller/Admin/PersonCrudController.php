<?php

namespace App\Controller\Admin;

use App\Entity\Person;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PersonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
	        IdField::new('id')
		        ->hideOnForm(),
	        TextField::new('firstName'),
	        TextField::new('lastName'),
	        IntegerField::new('idExternal'),
        ];
    }

	public function configureCrud(Crud $crud): Crud
	{
		return $crud
			// the labels used to refer to this entity in titles, buttons, etc.
			->setEntityLabelInSingular('Person')
			->setEntityLabelInPlural('People')
			->setDefaultSort([
				'lastName' => 'ASC',
				'firstName' => 'ASC',
			])

			// the Symfony Security permission needed to manage the entity
			// (none by default, so you can manage all instances of the entity)
			// ->setEntityPermission('ROLE_EDITOR')
			;
	}

	public function configureFilters(Filters $filters): Filters
	{
		return parent::configureFilters($filters)
			->add('firstName')
			->add('lastName')
			->add('idExternal');
	}
}
