<?php

namespace App\Controller\Admin\CrudController;

use App\Entity\Seat;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

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
		        ->setRequired(true),
	        IntegerField::new('coordX')
		        ->setLabel('X coordinate'),
	        IntegerField::new('coordY')
		        ->setLabel('Y coordinate'),
        ];
    }

	public function configureCrud(Crud $crud): Crud
	{
		return $crud
			->setEntityLabelInSingular('Seat')
			->setEntityLabelInPlural('Seats')
			->setDefaultSort([
				'id' => 'ASC',
			])
			;
	}

	public function configureFilters(Filters $filters): Filters
	{
		return parent::configureFilters($filters)
			->add('office');
	}
}
