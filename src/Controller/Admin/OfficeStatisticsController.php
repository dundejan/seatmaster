<?php

namespace App\Controller\Admin;

use App\Entity\Office;
use App\Helper\ChartHelper;
use App\Helper\ColorHelper;
use App\Repository\AssignmentRepository;
use App\Repository\OfficeRepository;
use App\Repository\RepeatedAssignmentRepository;
use App\Repository\SeatRepository;
use DateTime;
use DateTimeZone;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Exception;
use LogicException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class OfficeStatisticsController extends AbstractDashboardController
{
	private OfficeRepository $officeRepository;
	private AssignmentRepository $assignmentRepository;
	private RepeatedAssignmentRepository $repeatedAssignmentRepository;
	private ChartBuilderInterface $chartBuilder;
	private SeatRepository $seatRepository;

	public function __construct(
		OfficeRepository $officeRepository,
		AssignmentRepository $assignmentRepository,
		RepeatedAssignmentRepository $repeatedAssignmentRepository,
		ChartBuilderInterface $chartBuilder,
		SeatRepository $seatRepository,
	) {
		$this->officeRepository = $officeRepository;
		$this->assignmentRepository = $assignmentRepository;
		$this->repeatedAssignmentRepository = $repeatedAssignmentRepository;
		$this->chartBuilder = $chartBuilder;
		$this->seatRepository = $seatRepository;
	}

	/**
	 * @throws Exception
	 */
	#[Route('/admin/statistics/office-current', name: 'app_admin_office_statistics_index')]
	public function currentOccupancy(): Response
	{
		$offices = $this->officeRepository->findBy([], ['name' => 'ASC']);

		$officesArray = [];

		foreach ($offices as $office) {
			$officeArray = [
				'name' => $office->getName(),
				'currentPersons' =>
					count($this->assignmentRepository->findCurrentlyOngoing($office)) +
					count($this->repeatedAssignmentRepository->findCurrentlyOngoing($office)),
				'capacity' => count($office->getSeats()),
			];

			$officesArray[] = $officeArray;
		}

		return $this->render('admin/office_statistics.html.twig', [
			'offices' => $officesArray,
		]);
	}

	/**
	 * @throws Exception
	 */
	#[Route('/admin/statistics/office/{officeId}', name: 'app_admin_office_statistics_show')]
	public function show(int $officeId): Response
	{
		$office = $this->officeRepository->find($officeId);
		if ($office == null) {
			throw new LogicException('The requested office does not exist, although it is id was previously found.');
		}

		return $this->render('admin/office_statistics_show.html.twig', [
			'office' => $office,
			'currentPersons' => count($this->assignmentRepository->findCurrentlyOngoing($office)) +
				count($this->repeatedAssignmentRepository->findCurrentlyOngoing($office)),
			'capacity' => count($office->getSeats()),
			'chartToday' => $this->createChartToday($office),
			'chartMonth' => $this->createChartMonth($office),
		]);
	}

	/**
	 * @throws Exception
	 */
	#[Route('/admin/statistics/office-all', name: 'app_admin_office_statistics_all')]
	public function index(): Response
	{
		return $this->render('admin/office_statistics_index.html.twig', [
			'currentPersons' => count($this->assignmentRepository->findCurrentlyOngoing()) +
				count($this->repeatedAssignmentRepository->findCurrentlyOngoing()),
			'capacity' => count($this->seatRepository->findAll()),
			'chartToday' => $this->createChartAllOfficesToday(),
			'chartMonth' => $this->createChartAllOfficesMonth(),
		]);
	}

	/**
	 * @throws Exception
	 */
	private function createChartToday(Office $office): Chart
	{
		$chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
		$hoursInDay = array_map(fn($hour) => str_pad((string)$hour, 2, '0', STR_PAD_LEFT) . ':00', range(0, 23));

		$data = [];
		foreach (range(0, 23) as $hour) {
			$date = new DateTime('now', new DateTimeZone('UTC'));
			$date->setTime($hour, 0);
			$date->modify('-2 hours');
			$count = count($this->assignmentRepository->findOngoing($date, $date, $office))
				+ count($this->repeatedAssignmentRepository->findOngoing($date, $date, $office));
			$data[] = $count;
		}

		$currentHour = (new DateTime('now', new DateTimeZone('Europe/Paris')))->format('H') . ':00';

		$chart->setData([
			'labels' => $hoursInDay,
			'datasets' => [[
				'label' => 'Number of occupied seats in ' . $office->getName(),
				'backgroundColor' => 'rgb(255, 99, 132)',  // pink
				'borderColor' => 'rgb(255, 99, 132)',  // pink
				'data' => $data,
			]],
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
						'text' => 'Seats',
					],
					'suggestedMin' => 0,
					'suggestedMax' => count($office->getSeats()),
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
	private function createChartMonth(Office $office): Chart
	{
		$chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

		// Generate array of days with last 20 days and next 10 days
		$days = array_map(fn($day) => (new DateTime('now', new DateTimeZone('UTC')))->modify("$day days")->format('Y-m-d'), range(-20, 9));

		$data = [];

		foreach ($days as $day) {
			$sum = 0;
			foreach (range(8, 20) as $hour) {
				$startDate = new DateTime("$day $hour:00:00", new DateTimeZone('UTC'));
				$endDate = new DateTime("$day $hour:59:59", new DateTimeZone('UTC'));

				$count = count($this->assignmentRepository->findOngoing($startDate, $endDate, $office))
					+ count($this->repeatedAssignmentRepository->findOngoing($startDate, $endDate, $office));

				$sum += $count;
			}
			$average = $sum / 13; // 20 - 8 + 1 = 13
			$data[] = $average;
		}

		$currentDay = (new DateTime('now', new DateTimeZone('Europe/Paris')))->format('Y-m-d');

		$chart->setData([
			'labels' => $days, // array_reverse to show the earliest day first
			'datasets' => [
				[
					'label' => 'Average occupation between 8:00 and 20:00 in ' . $office->getName(),
					'backgroundColor' => 'rgb(52, 152, 219)',  // light blue
					'borderColor' => 'rgb(52, 152, 219)',  // light blue
					'data' => $data,
				],
			],
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
						'text' => 'Seats',
					],
					'suggestedMin' => 0,
					'suggestedMax' => count($office->getSeats()),
				],
			],
		]);

		ChartHelper::addPluginZoom($chart);
		ChartHelper::addPluginAnnotation($chart, $currentDay);

		return $chart;
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