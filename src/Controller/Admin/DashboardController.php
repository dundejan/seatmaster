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
use DateTime;
use DateTimeZone;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractDashboardController
{
	private PersonRepository $personRepository;
	private OfficeRepository $officeRepository;
	private SeatRepository $seatRepository;
	private AssignmentRepository $assignmentRepository;
	private RepeatedAssignmentRepository $repeatedAssignmentRepository;
	private ChartBuilderInterface $chartBuilder;

	public function __construct(
		PersonRepository $personRepository,
		OfficeRepository $officeRepository,
		SeatRepository $seatRepository,
		AssignmentRepository $assignmentRepository,
		RepeatedAssignmentRepository $repeatedAssignmentRepository,
		ChartBuilderInterface $chartBuilder,
	) {
		$this->personRepository = $personRepository;
		$this->officeRepository = $officeRepository;
		$this->seatRepository = $seatRepository;
		$this->assignmentRepository = $assignmentRepository;
		$this->repeatedAssignmentRepository = $repeatedAssignmentRepository;
		$this->chartBuilder = $chartBuilder;
	}

	/**
	 * @throws \Exception
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
			'chart' => $this->createChart(),
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
		$offices = $this->officeRepository->findAll();  // Assuming you've injected officeRepository

		foreach ($offices as $office) {
			$officeSubMenuItems[] = MenuItem::linkToRoute(
				$office->getName(), // label
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

	/**
	 * @throws \Exception
	 */
	private function createChart(): Chart
	{
		$chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

		$hoursInDay = array_map(fn($hour) => str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00', range(0, 23));

		// Initializing data array for sum of all offices
		$totalData = array_fill(0, 24, 0);

		$allDatasets = [];
		$colors = $this->getNiceColors();
		$colorIndex = 0;

		$currentHour = (new DateTime('now', new DateTimeZone('Europe/Paris')))->format('H') . ':00';

		// Loop through each office and create its dataset
		$offices = $this->officeRepository->findAll();
		foreach ($offices as $office) {
			$data = [];
			foreach (range(0, 23) as $hour) {
				$date = new DateTime('now', new DateTimeZone('UTC'));
				$date->setTime($hour, 0, 0);
				$date->modify('-2 hours');
				$count = count($this->assignmentRepository->findOngoing($date, $date, $office))
					+ count($this->repeatedAssignmentRepository->findOngoing($date, $date, $office));
				$data[] = $count;
				$totalData[$hour] += $count;
			}

			$allDatasets[] = [
				'label' => $office->getName(),
				'backgroundColor' => $colors[$colorIndex],
				'borderColor' => $colors[$colorIndex],
				'data' => $data,
			];

			$colorIndex++;
		}

		// Add dataset for sum of all offices
		$allDatasets[] = [
			'label' => 'All Offices',
			'backgroundColor' => 'rgb(0, 0, 0)',
			'borderColor' => 'rgb(0, 0, 0)',
			'data' => $totalData,
		];

		$chart->setData([
			'labels' => $hoursInDay,
			'datasets' => $allDatasets,
		]);

		$chart->setOptions([
			'scales' => [
				'x' => [
					'title' => [
						'display' => true,
						'text' => 'Time (today)',
					],
				],
				'y' => [
					'title' => [
						'display' => true,
						'text' => 'Number of occupied seats',
					],
					'suggestedMin' => 0,
					'suggestedMax' => $this->seatRepository->count([]),
				],
			],
			'plugins' => [
				'zoom' => [
					'zoom' => [
						'wheel' => ['enabled' => true],
						'pinch' => ['enabled' => true],
						'mode' => 'xy',
					],
					'pan' => [
						'enabled' => true,
						'mode' => 'xy',
						'threshold' => 10, // Minimum amount of pixels the user must pan before it starts panning.
					],
				],
				'annotation' => [
					'annotations' => [
						[
							'type' => 'line',
							'mode' => 'vertical',
							'scaleID' => 'x',
							'value' => $currentHour,
							'borderColor' => 'rgb(217, 120, 23)',
							'borderWidth' => 1.5,
							'label' => [
								'enabled' => true,
								'content' => 'Current hour'
							]
						],
					],
				],
			],
		]);

		return $chart;
	}

	/**
	 * @return string[]
	 */
	function getNiceColors(): array {
		return [
			'rgb(255, 99, 132)',  // pink
			'rgb(75, 192, 192)',  // teal
			'rgb(255, 159, 64)',  // orange
			'rgb(153, 102, 255)', // purple
			'rgb(54, 162, 235)',  // blue
			'rgb(255, 206, 86)',  // yellow
			'rgb(231, 76, 60)',   // red
			'rgb(46, 204, 113)',  // green
			'rgb(52, 152, 219)',  // light blue
			'rgb(155, 89, 182)',  // lavender
			'rgb(241, 196, 15)',  // sunflower yellow
			'rgb(26, 188, 156)',  // turquoise
			'rgb(22, 160, 133)',  // green sea
			'rgb(52, 73, 94)',    // wet asphalt
			'rgb(192, 57, 43)',   // alizarin
			'rgb(189, 195, 199)', // gray
			'rgb(243, 156, 18)',  // orange
			'rgb(142, 68, 173)',  // wisteria
			'rgb(44, 62, 80)',    // midnight blue
			'rgb(211, 84, 0)',    // pumpkin orange
		];
	}
}
