<?php

namespace App\Controller;

use App\Repository\AssignmentRepository;
use App\Repository\OfficeRepository;
use App\Repository\RepeatedAssignmentRepository;
use App\Repository\SeatRepository;
use DateTime;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class AssignmentController extends AbstractController
{
	/**
	 * @Route("/api/ongoing_assignments/office/{officeId?}", methods={"GET"}, name="api_ongoing_office_assignments")
	 */
	public function getOngoingAssignmentsForOffice(
		?int $officeId,
		RepeatedAssignmentRepository $repeatedAssignmentRepository,
		AssignmentRepository $assignmentRepository,
		OfficeRepository $officeRepository,
		SerializerInterface $serializer
	): JsonResponse {
		$office = null;

		if ($officeId !== null) {
			$office = $officeRepository->find($officeId);
			if (!$office) {
				return new JsonResponse(['error' => 'Office not found'], 404);
			}
		}

		$assignments = $assignmentRepository->findCurrentlyOngoing($office);
		$repeatedAssignments = $repeatedAssignmentRepository->findCurrentlyOngoing($office);

		$allAssignments = array_merge($assignments, $repeatedAssignments);

		$data = $serializer->serialize($allAssignments, 'json');

		return new JsonResponse($data, 200, [], true);
	}

	/**
	 * @throws \Exception
	 */
	#[Route('/ongoing_assignments/seat/{seatId}', name: 'ongoing_seat_assignments')]
	public function getOngoingAssignmentsForSeat(
		?int $seatId,
		Request $request, // Add Symfony's Request object to get query parameters
		RepeatedAssignmentRepository $repeatedAssignmentRepository,
		AssignmentRepository $assignmentRepository,
		SeatRepository $seatRepository,
		SerializerInterface $serializer
	): JsonResponse {
		$seat = null;

		if ($seatId !== null) {
			$seat = $seatRepository->find($seatId);
			if (!$seat) {
				return new JsonResponse(['error' => 'Office not found'], 404);
			}
		}

		// Retrieve DateTime from query parameter, if present
		/** @var string|null $dateTimeParam */
		$dateTimeParam = $request->query->get('dateTimeParam');

		$timeToUse = $dateTimeParam ?
			new DateTime($dateTimeParam, new DateTimeZone('UTC')) :
			new DateTime('now', new DateTimeZone('UTC'));

		$assignments = $assignmentRepository->findOngoing($timeToUse, $timeToUse, $seat);
		$repeatedAssignments = $repeatedAssignmentRepository->findOngoing($timeToUse, $timeToUse, $seat);

		$allAssignments = array_merge($assignments, $repeatedAssignments);

		$data = $serializer->serialize($allAssignments, 'json');

		return new JsonResponse($data, 200, [], true);
	}

	/**
	 * @throws \Exception
	 */
	#[Route('/ongoing_assignments/all', name: 'ongoing_assignments')]
	public function getOngoingAssignments(
		\DateTime $from,
		\DateTime $to,
		RepeatedAssignmentRepository $repeatedAssignmentRepository,
		AssignmentRepository $assignmentRepository,
		SerializerInterface $serializer,
	): JsonResponse {
		$allAssignments = $assignmentRepository->findOngoing($from, $to);

		$data = $serializer->serialize($allAssignments, 'json');

		return new JsonResponse($data, 200, [], true);
	}
}