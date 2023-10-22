<?php

namespace App\Controller\Admin\CrudController;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
	    $roles = ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER'];

	    return [
		    IdField::new('id')
			    ->hideOnForm(),

		    TextField::new('email')
			    ->setFormTypeOption('disabled', true),

		    ChoiceField::new('roles')
			    ->setChoices(array_combine($roles, $roles))
			    ->allowMultipleChoices()
			    ->renderExpanded()
			    ->renderAsBadges()
	    ];
    }

	public function configureActions(Actions $actions): Actions
	{
		$thisUser = $this->getUser();

		// Condition function for editing user
		$updateDisplayCondition = function (Action $action) use ($thisUser) {
			$action->displayIf(static function (User $user) use ($thisUser) {
				return $user !== $thisUser;
			});
			return $action;
		};

		// Condition function for deleting user
		$deleteDisplayCondition = function (Action $action) {
			$action->displayIf(static function (User $user) {
				return !$user->hasRole('ROLE_SUPER_ADMIN');
			});
			return $action;
		};

		return parent::configureActions($actions)
			->setPermission(Action::EDIT, 'ROLE_SUPER_ADMIN')
			->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
			->setPermission(Action::BATCH_DELETE, 'ROLE_SUPER_ADMIN')
			->disable(Action::NEW)
			->update(Crud::PAGE_INDEX, Action::EDIT, $updateDisplayCondition)
			->update(Crud::PAGE_DETAIL, Action::EDIT, $updateDisplayCondition)
			->update(Crud::PAGE_INDEX, Action::DELETE, $deleteDisplayCondition)
			->update(Crud::PAGE_DETAIL, Action::DELETE, $deleteDisplayCondition)
			;
	}

	/**
	 * @param User $entityInstance
	 */
	public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
	{
		if ($entityInstance === $this->getUser()) {
			throw new AccessDeniedException('You can not edit yourself from admin section.');
		}

		parent::updateEntity($entityManager, $entityInstance);
	}

	/**
	 * @param User $entityInstance
	 */
	public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
	{
		if ($entityInstance->hasRole('ROLE_SUPER_ADMIN')) {
			throw new AccessDeniedException('Deleting user with ROLE_SUPER_ADMIN is forbidden, first you need to degrade his role.');
		}

		parent::deleteEntity($entityManager, $entityInstance);
	}
}
