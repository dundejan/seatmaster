<?php

namespace App\Controller\Admin;

use App\Controller\Admin\CrudController\AssignmentCrudController;
use App\Controller\Admin\CrudController\OngoingAssignmentsCrudController;
use App\Controller\Admin\CrudController\OngoingRepeatedAssignmentsCrudController;
use App\Controller\Admin\CrudController\RepeatedAssignmentCrudController;
use App\Entity\ApiToken;
use App\Entity\Assignment;
use App\Entity\Office;
use App\Entity\Person;
use App\Entity\RepeatedAssignment;
use App\Entity\Seat;
use App\Entity\User;
use App\Repository\AssignmentRepository;
use App\Repository\OfficeRepository;
use App\Repository\PersonRepository;
use App\Repository\RepeatedAssignmentRepository;
use App\Repository\SeatRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
	private PersonRepository $personRepository;
	private OfficeRepository $officeRepository;
	private SeatRepository $seatRepository;
	private AssignmentRepository $assignmentRepository;
	private RepeatedAssignmentRepository $repeatedAssignmentRepository;

	public function __construct(
		PersonRepository $personRepository,
		OfficeRepository $officeRepository,
		SeatRepository $seatRepository,
		AssignmentRepository $assignmentRepository,
		RepeatedAssignmentRepository $repeatedAssignmentRepository,
	) {
		$this->personRepository = $personRepository;
		$this->officeRepository = $officeRepository;
		$this->seatRepository = $seatRepository;
		$this->assignmentRepository = $assignmentRepository;
		$this->repeatedAssignmentRepository = $repeatedAssignmentRepository;
	}

	/**
	 * @throws Exception
	 */
	#[Route('/admin', name: 'admin')]
    public function index(): Response
    {
	    $statistics = [
		    'totalPersons' => $this->personRepository->count([]),
		    'totalOffices' => $this->officeRepository->count([]),
		    'totalSeats' => $this->seatRepository->count([]),
		    'currentlyOngoingAssignments' =>
			    count($this->assignmentRepository->findCurrentlyOngoing())
		        + count($this->repeatedAssignmentRepository->findCurrentlyOngoing()),
	    ];

		return $this->render('admin/dashboard.html.twig', [
			'statistics' => $statistics,
		]);
    }

	public function configureDashboard(): Dashboard
	{
		return Dashboard::new()
			->setTitle('ADMIN')
			->setFaviconPath('images/boss-icon.svg')
			;
	}

	public function configureMenuItems(): iterable
	{
		$officeSubMenuItems = [];
		$officeSubMenuItems[] = MenuItem::linkToRoute(
			'All', // label
			'fa fa-list', // icon
			'app_admin_office_statistics_all' // route
		);

		$offices = $this->officeRepository->findBy([], ['name' => 'ASC']);  // Assuming you've injected officeRepository

		foreach ($offices as $office) {
			$officeSubMenuItems[] = MenuItem::linkToRoute(
				(string)$office->getName(), // label
				'fa fa-building', // icon
				'app_admin_office_statistics_show', // route
				['officeId' => $office->getId()] // parameters
			);
		}

		return [
			MenuItem::section('Info'),
			MenuItem::linkToDashboard('Statistics', 'fa fa-chart-simple'),
			MenuItem::linkToRoute('Current occupancy', 'fa fa-building-user','app_admin_office_statistics_index'),
			MenuItem::subMenu('Office statistics', 'fa fa-building')->setSubItems($officeSubMenuItems),

			MenuItem::section('Management'),
			MenuItem::linkToCrud('Offices', 'fa fa-building', Office::class),

			MenuItem::linkToCrud('Seats', 'fas fa-chair', Seat::class),

			MenuItem::linkToCrud('People', 'fa fa-person', Person::class),

			MenuItem::subMenu('One-time assignments', 'fa fa-calendar')
				->setSubItems([
					MenuItem::linkToCrud('All', 'fa fa-list', Assignment::class)
						->setDefaultSort(['id' => 'DESC'])
						->setController(AssignmentCrudController::class),

					MenuItem::linkToCrud('Ongoing', 'fa fa-calendar-check', Assignment::class)
						->setDefaultSort(['id' => 'DESC'])
						->setController(OngoingAssignmentsCrudController::class),
				]),

			MenuItem::subMenu('Repeated assignments', 'fa fa-calendar-day')
				->setSubItems([
					MenuItem::linkToCrud('All', 'fa fa-list', RepeatedAssignment::class)
						->setDefaultSort(['id' => 'DESC'])
						->setController(RepeatedAssignmentCrudController::class),

					MenuItem::linkToCrud('Ongoing', 'fa fa-calendar-check', RepeatedAssignment::class)
						->setDefaultSort(['id' => 'DESC'])
						->setController(OngoingRepeatedAssignmentsCrudController::class),
				]),

			MenuItem::linkToCrud('Users', 'fa fa-user', User::class),

			MenuItem::section('Api'),
			MenuItem::linkToCrud('API tokens', 'fa fa-key', ApiToken::class),

			MenuItem::section('Navigation'),
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
