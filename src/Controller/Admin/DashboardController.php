<?php

namespace App\Controller\Admin;

use App\Entity\Assignment;
use App\Entity\Office;
use App\Entity\Person;
use App\Entity\RepeatedAssignment;
use App\Entity\Seat;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
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
		return $this->render('admin/dashboard.html.twig');
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
			MenuItem::section('Management'),
			MenuItem::linkToCrud('Offices', 'fa fa-building', Office::class),

			MenuItem::linkToCrud('Seats', 'fas fa-chair', Seat::class),

			MenuItem::linkToCrud('People', 'fa fa-person', Person::class),

			MenuItem::linkToCrud('Assignments', 'fa fa-calendar', Assignment::class)
				->setDefaultSort(['id' => 'DESC']),

			MenuItem::linkToCrud('Repeated assignments', 'fa fa-calendar-day', RepeatedAssignment::class)
				->setDefaultSort(['id' => 'DESC']),

			MenuItem::section('Navigation')->setCssClass('navigation'),
			MenuItem::linkToUrl('Back to app', 'fa fa-arrow-left', $this->generateUrl('app_homepage')),
		];
	}

	public function configureActions(): Actions
	{
		return parent::configureActions()
			->add(Crud::PAGE_INDEX, Action::DETAIL)
			;
	}

	public function configureAssets(): Assets
	{
		return parent::configureAssets()
			->addWebpackEncoreEntry('admin')
			;
	}
}
