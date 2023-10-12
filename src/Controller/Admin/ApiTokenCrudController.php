<?php

namespace App\Controller\Admin;

use App\Entity\ApiToken;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ApiTokenCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ApiToken::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
	        FormField::addPanel('Warning! Creating or editing API tokens manually is not recommended.')
		        ->setHelp('If you plan to do that, make sure you know what you are doing ;-)')
		        ->setFormTypeOption('disabled', true), // Disables the input field
            IdField::new('id')
	            ->hideOnForm(),
	        TextField::new('token')
		        ->hideOnIndex()
		        ->setDisabled(),
            AssociationField::new('ownedBy'),
            DateTimeField::new('expiresAt'),
        ];
    }

	public function configureActions(Actions $actions): Actions
	{
		return parent::configureActions($actions)
			->disable(Action::BATCH_DELETE)
			// disable delete on index page, it should be available just on edit page, where is shown warning
			->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
				$action->displayIf(function () {
					return false;
				});
				return $action;
			})
			;
	}

	public function configureCrud(Crud $crud): Crud
	{
		return $crud
			// the labels used to refer to this entity in titles, buttons, etc.
			->setEntityLabelInSingular('API Token')
			->setEntityLabelInPlural('API Tokens')
			->setEntityPermission('ROLE_SUPER_ADMIN')

			// the Symfony Security permission needed to manage the entity
			// (none by default, so you can manage all instances of the entity)
			// ->setEntityPermission('ROLE_EDITOR')
			;
	}
}
