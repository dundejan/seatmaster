<?php

namespace App\Controller\Admin;

use App\Entity\Office;
use App\Repository\AssignmentRepository;
use App\Repository\OfficeRepository;
use App\Repository\RepeatedAssignmentRepository;
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

	public function __construct(
		OfficeRepository $officeRepository,
		AssignmentRepository $assignmentRepository,
		RepeatedAssignmentRepository $repeatedAssignmentRepository,
		ChartBuilderInterface $chartBuilder,
	) {
		$this->officeRepository = $officeRepository;
		$this->assignmentRepository = $assignmentRepository;
		$this->repeatedAssignmentRepository = $repeatedAssignmentRepository;
		$this->chartBuilder = $chartBuilder;
	}

	/**
	 * @throws Exception
	 */
	#[Route('/admin/office-statistics', name: 'app_admin_office_statistics_index')]
	public function index(): Response
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
	#[Route('/admin/statistics/{officeId}', name: 'app_admin_office_statistics_show')]
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
			'chart' => $this->createChart($office),
		]);
	}

	/**
	 * @throws Exception
	 */
	private function createChart(Office $office): Chart
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
				'label' => $office->getName(),
				'backgroundColor' => 'rgb(0, 0, 0)',
				'borderColor' => 'rgb(0, 0, 0)',
				'data' => $data,
			]],
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
					'suggestedMax' => count($office->getSeats()),
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
}