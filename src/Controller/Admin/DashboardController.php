<?php

namespace App\Controller\Admin;

use App\Entity\Assignment;
use App\Entity\Office;
use App\Entity\Person;
use App\Entity\Seat;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{

	#[Route('/admin', name: 'admin')]
    public function index(): Response
    {
	    $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

	    return $this->redirect($adminUrlGenerator->setController(OfficeCrudController::class)->generateUrl());

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

	public function configureDashboard(): Dashboard
	{
		return Dashboard::new()
			->setTitle('ADMIN')
			->setFaviconPath('boss-icon.svg')
			;
	}

	public function configureMenuItems(): iterable
	{
		return [
			MenuItem::linkToCrud('Offices', 'fa fa-building', Office::class),

			MenuItem::linkToCrud('Seats', 'fas fa-chair', Seat::class),

			MenuItem::linkToCrud('People', 'fa fa-person', Person::class),

			MenuItem::linkToCrud('Assignments', 'fa fa-calendar', Assignment::class)
				->setDefaultSort(['id' => 'DESC']),

			MenuItem::linkToUrl('Back to app', 'fa fa-arrow-left', $this->generateUrl('app_homepage')),
		];
	}

	public function configureActions(): Actions
	{
		return parent::configureActions()
			->add(Crud::PAGE_INDEX, Action::DETAIL)
			;
	}
}
