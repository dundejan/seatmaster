<?php

namespace App\Controller\Admin;

use App\Repository\AssignmentRepository;
use App\Repository\OfficeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OfficeStatisticsController extends AbstractDashboardController
{
	private OfficeRepository $officeRepository;
	private AssignmentRepository $assignmentRepository;

	public function __construct(
		OfficeRepository $officeRepository,
		AssignmentRepository $assignmentRepository,
	) {
		$this->officeRepository = $officeRepository;
		$this->assignmentRepository = $assignmentRepository;
	}

	#[Route('/admin/office-statistics', name: 'app_admin_office_statistics_index')]
	public function index(): Response
	{
		$offices = $this->officeRepository->findAll();

		$officesArray = [];

		foreach ($offices as $index => $office) {
			$officeArray = [
				'name' => $office->getName(),
				'currentPersons' => count($this->assignmentRepository->findCurrentlyOngoing($office)),
				'capacity' => count($office->getSeats()),
			];

			$officesArray[] = $officeArray;
		}

		return $this->render('admin/office_statistics.html.twig', [
			'offices' => $officesArray,
		]);
	}
}