<?php

namespace App\Controller;

use App\Entity\Seat;
use App\Repository\AssignmentRepository;
use App\Repository\RepeatedAssignmentRepository;
use App\Repository\SeatRepository;
use DateTime;
use DateTimeZone;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
	public function show(): Response
	{
		$now = new DateTime('now', new DateTimeZone('UTC'));
		$currentAssignments = $this->assignmentRepository->findOngoing($now, $now);
		$currentRepeatedAssignments = $this->repeatedAssignmentRepository->findCurrentlyOngoing();

		$allCurrentAssignments = array_merge($currentAssignments, $currentRepeatedAssignments);

		// Collect the IDs of seats that are in RepeatedAssignment.
		$assignedSeatIds = array_map(function($assignment) {
			return $assignment->getSeat()->getId();
		}, $allCurrentAssignments);

		$allSeats = $this->seatRepository->findAll();

		// Filter out those seats from the second array.
		$availableSeats = array_filter($allSeats, function($seat) use ($assignedSeatIds) {
			return !in_array($seat->getId(), $assignedSeatIds);
		});

		// Re-index the array to make it 0-based again
		$availableSeats = array_values($availableSeats);

		return $this->render('seat/available.html.twig', [
			"availableSeats" => $availableSeats,
		]);
	}
}