<?php

namespace App\Controller\Admin\CrudController;

use App\Entity\Seat;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class SeatCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Seat::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
	            ->hideOnForm(),
	        AssociationField::new('office')
		        ->autocomplete()
		        ->setRequired(false),
        ];
    }

	public function configureCrud(Crud $crud): Crud
	{
		return $crud
			// the labels used to refer to this entity in titles, buttons, etc.
			->setEntityLabelInSingular('Seat')
			->setEntityLabelInPlural('Seats')
			->setDefaultSort([
				'id' => 'ASC',
			])

			// the Symfony Security permission needed to manage the entity
			// (none by default, so you can manage all instances of the entity)
			// ->setEntityPermission('ROLE_EDITOR')
			;
	}

	public function configureFilters(Filters $filters): Filters
	{
		return parent::configureFilters($filters)
			->add('office');
	}
}
