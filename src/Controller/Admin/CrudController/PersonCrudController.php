<?php

namespace App\Controller\Admin\CrudController;

use App\Entity\Person;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
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
	        EmailField::new('email'),
	        TextField::new('jobTitle'),
	        IntegerField::new('idExternal'),
        ];
    }

	public function configureCrud(Crud $crud): Crud
	{
		return $crud
			->setEntityLabelInSingular('Person')
			->setEntityLabelInPlural('People')
			->setDefaultSort([
				'lastName' => 'ASC',
				'firstName' => 'ASC',
			])
			;
	}

	public function configureFilters(Filters $filters): Filters
	{
		return parent::configureFilters($filters)
			->add('firstName')
			->add('lastName')
			->add('email')
			->add('jobTitle')
			->add('idExternal');
	}
}
