<?php

namespace App\Controller;

use App\Repository\AssignmentRepository;
use App\Repository\RepeatedAssignmentRepository;
use App\Repository\SeatRepository;
use DateTime;
use DateTimeZone;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SeatController extends AbstractController
{
	public function __construct(
		private readonly AssignmentRepository $assignmentRepository,
		private readonly RepeatedAssignmentRepository $repeatedAssignmentRepository,
		private readonly SeatRepository $seatRepository,
	){
	}

	/**
	 * @throws Exception
	 */
	#[Route('/seats-free', name: 'app_seats_free', methods: ['GET'])]
	public function show(Request $request): Response
	{
		$fromDateTimeString = $request->query->get('fromDateTime');
		$toDateTimeString = $request->query->get('toDateTime');

		$dateTimeZone = new DateTimeZone('Europe/Paris');

		if ($fromDateTimeString !== null && !is_string($fromDateTimeString)) {
			throw new BadRequestHttpException("Invalid 'fromDateTime' parameter.");
		}
		if ($toDateTimeString !== null && !is_string($toDateTimeString)) {
			throw new BadRequestHttpException("Invalid 'toDateTime' parameter.");
		}

		$fromDateTime = $fromDateTimeString ? new DateTime($fromDateTimeString, $dateTimeZone) : new DateTime('now', $dateTimeZone);
		$toDateTime = $toDateTimeString ? new DateTime($toDateTimeString, $dateTimeZone) : new DateTime('now', $dateTimeZone);

		// TODO: test this, need to modify DateTimeZone or do +-2 hours to call findOngoing on assignmentRepository
		$currentAssignments = $this->assignmentRepository->findOngoing($fromDateTime, $toDateTime);
		$currentRepeatedAssignments = $this->repeatedAssignmentRepository->findOngoing($fromDateTime, $toDateTime);

		$allCurrentAssignments = array_merge($currentAssignments, $currentRepeatedAssignments);

		// Collect the IDs of seats that are in RepeatedAssignment.
		$assignedSeatIds = array_map(function($assignment) {
			return $assignment->getSeat()->getId();
		}, $allCurrentAssignments);

		$allSeats = $this->seatRepository->findBy([], ['id' => 'ASC']);

		// Filter out those seats from the second array.
		$availableSeats = array_filter($allSeats, function($seat) use ($assignedSeatIds) {
			return !in_array($seat->getId(), $assignedSeatIds);
		});

		// Re-index the array to make it 0-based again
		$availableSeats = array_values($availableSeats);

		return $this->render('seat/available.html.twig', [
			"availableSeats" => $availableSeats,
			"fromDateTime" => $fromDateTime->format('Y-m-d\TH:i'),
			"toDateTime" => $toDateTime->format('Y-m-d\TH:i'),
		]);
	}
}