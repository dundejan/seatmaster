<?php

namespace App\Controller\Admin\CrudController;

use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class ApiTokenCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ApiToken::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
	            ->hideOnForm(),
	        TextField::new('token')
		        ->hideOnIndex()
		        ->setDisabled(),
            AssociationField::new('ownedBy'),
            DateTimeField::new('expiresAt'),
	        FormField::addPanel('Info:')
		        ->setHelp('
					<b>Token permissions: </b> API token has always the same permissions as it`s owner.<br>
					<b>Visibility</b> API token is visible only to his owner. Only super-admins can see and edit all tokens.')
		        ->setFormTypeOption('disabled', true),
        ];
    }

	public function configureActions(Actions $actions): Actions
	{
		/**
		 * @var User $thisUser
		 */
		$thisUser = $this->getUser();

		// Condition function for showing API token user
		$detailDisplayCondition = function (Action $action) use ($thisUser) {
			$action->displayIf(static function (ApiToken $apiToken) use ($thisUser) {
				return ($thisUser->hasRole('ROLE_SUPER_ADMIN') || $apiToken->getOwnedBy() === $thisUser);
			});
			return $action;
		};

		return parent::configureActions($actions)
			->setPermission(Action::NEW, 'ROLE_SUPER_ADMIN')
			->setPermission(Action::EDIT, 'ROLE_SUPER_ADMIN')
			->disable(Action::BATCH_DELETE)
			// disable delete on index page, it should be available just on edit page, where is shown warning
			->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
				$action->displayIf(function () {
					return false;
				});
				return $action;
			})
			->update(Crud::PAGE_INDEX, Action::DETAIL, $detailDisplayCondition)
			;
	}

	public function configureCrud(Crud $crud): Crud
	{
		return $crud
			// the labels used to refer to this entity in titles, buttons, etc.
			->setEntityLabelInSingular('API Token')
			->setEntityLabelInPlural('API Tokens')

			// the Symfony Security permission needed to manage the entity
			// (none by default, so you can manage all instances of the entity)
			// ->setEntityPermission('ROLE_EDITOR')
			;
	}

	public function createEntity(string $entityFqcn)
	{
		/**
		 * @var User $user
		 */
		$user = $this->getUser();

		if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
			throw new AccessDeniedException('You can not edit yourself from admin section.');
		}

		return parent::createEntity($entityFqcn);
	}

	/**
	 * @param ApiToken $entityInstance
	 */
	public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
	{
		/**
		 * @var User $user
		 */
		$user = $this->getUser();

		if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
			throw new AccessDeniedException('You can not edit yourself from admin section.');
		}

		parent::updateEntity($entityManager, $entityInstance);
	}

	/**
	 * @param ApiToken $entityInstance
	 */
	public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
	{
		/**
		 * @var User $user
		 */
		$user = $this->getUser();

		if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
			throw new AccessDeniedException('You can not edit yourself from admin section.');
		}


		parent::deleteEntity($entityManager, $entityInstance);
	}
}
