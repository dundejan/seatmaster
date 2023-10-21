<?php

namespace App\Controller\Admin\CrudController;

use App\Entity\Office;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OfficeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Office::class;
    }

	public function configureCrud(Crud $crud): Crud
	{
		return $crud
			// the labels used to refer to this entity in titles, buttons, etc.
			->setEntityLabelInSingular('Office')
			->setEntityLabelInPlural('Offices')

			// the Symfony Security permission needed to manage the entity
			// (none by default, so you can manage all instances of the entity)
			// ->setEntityPermission('ROLE_EDITOR')
			;
	}

	public function configureFields(string $pageName): iterable
	{
		return [
			IdField::new('id')
				->hideOnForm(),
			TextField::new('name'),
			IntegerField::new('height'),
			IntegerField::new('width'),
		];
	}

	public function configureFilters(Filters $filters): Filters
	{
		return parent::configureFilters($filters)
			->add('height')
			->add('width');
	}
}
