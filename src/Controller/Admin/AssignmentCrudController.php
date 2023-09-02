<?php

namespace App\Controller\Admin;

use App\Entity\Assignment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AssignmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Assignment::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
