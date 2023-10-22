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
use App\Helper\ChartHelper;
use App\Helper\ColorHelper;
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
use Exception;
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
			'chartDay' => $this->createChartAllOfficesToday(),
			'chartMonth' => $this->createChartAllOfficesMonth(),
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

	/**
	 * @throws Exception
	 */
	private function createChartAllOfficesToday(): Chart
	{
		$chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

		$hoursInDay = array_map(fn($hour) => str_pad((string)$hour, 2, '0', STR_PAD_LEFT) . ':00', range(0, 23));

		// Initializing data array for sum of all offices
		$totalData = array_fill(0, 24, 0);

		$allDatasets = [];
		$colors = ColorHelper::getNiceColors();
		$colorIndex = 0;

		$currentHour = (new DateTime('now', new DateTimeZone('Europe/Paris')))->format('H') . ':00';

		// Loop through each office and create its dataset
		$offices = $this->officeRepository->findAll();
		foreach ($offices as $office) {
			$data = [];
			foreach (range(0, 23) as $hour) {
				$date = new DateTime('now', new DateTimeZone('UTC'));
				$date->setTime($hour, 0);
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
						'text' => 'Time of today '
							. '('
							. (new DateTime('now', new DateTimeZone('Europe/Paris')))->format('Y-m-d')
							. ')',
					],
				],
				'y' => [
					'title' => [
						'display' => true,
						'text' => 'Occupied seats',
					],
					'suggestedMin' => 0,
					'suggestedMax' => $this->seatRepository->count([]),
				],
			],
		]);

		ChartHelper::addPluginZoom($chart);
		ChartHelper::addPluginAnnotation($chart, $currentHour);

		return $chart;
	}

	/**
	 * @throws Exception
	 */
	private function createChartAllOfficesMonth(): Chart
	{
		$chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

		// Generate array of days with last 20 days and next 10 days
		$days = array_map(fn($day) => (new DateTime('now', new DateTimeZone('UTC')))->modify("$day days")->format('Y-m-d'), range(-20, 9));

		$allDatasets = [];
		$totalData = array_fill(0, count($days), 0);
		$colors = ColorHelper::getNiceColors();
		$colorIndex = 0;

		// Loop through each office
		$offices = $this->officeRepository->findAll();
		foreach ($offices as $office) {
			$data = [];
			foreach ($days as $dayIndex => $day) {
				$sum = 0;
				foreach (range(8, 20) as $hour) {
					$startDate = new DateTime("$day $hour:00:00", new DateTimeZone('UTC'));
					$endDate = new DateTime("$day $hour:59:59", new DateTimeZone('UTC'));

					$count = count($this->assignmentRepository->findOngoing($startDate, $endDate, $office))
						+ count($this->repeatedAssignmentRepository->findOngoing($startDate, $endDate, $office));

					$sum += $count;
				}
				$average = $sum / 13;
				$data[] = $average;
				$totalData[$dayIndex] += $average;
			}

			$allDatasets[] = [
				'label' => $office->getName(),
				'backgroundColor' => $colors[$colorIndex],
				'borderColor' => $colors[$colorIndex],
				'data' => $data,
			];

			$colorIndex++;
		}

		// Add dataset for all offices
		$allDatasets[] = [
			'label' => 'All Offices',
			'backgroundColor' => 'rgb(0, 0, 0)',
			'borderColor' => 'rgb(0, 0, 0)',
			'data' => $totalData,
		];

		$chart->setData([
			'labels' => $days,
			'datasets' => $allDatasets,
		]);

		$chart->setOptions([
			'scales' => [
				'x' => [
					'title' => [
						'display' => true,
						'text' => 'Day',
					],
				],
				'y' => [
					'title' => [
						'display' => true,
						'text' => 'Average occupied seats between 8:00 and 20:00',
					],
					'suggestedMin' => 0,
					'suggestedMax' => $this->seatRepository->count([]),
				],
			],
		]);

		ChartHelper::addPluginZoom($chart);
		ChartHelper::addPluginAnnotation($chart, (new DateTime('now', new DateTimeZone('Europe/Paris')))->format('Y-m-d'));

		return $chart;
	}
}
