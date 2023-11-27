<?php

namespace App\Controller;

use App\Repository\AssignmentRepository;
use App\Repository\RepeatedAssignmentRepository;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PersonRepository;

class PersonController extends AbstractController
{
	private PersonRepository $personRepository;
	private AssignmentRepository $assignmentRepository;
	private RepeatedAssignmentRepository $repeatedAssignmentRepository;

	public function __construct(
		PersonRepository $personRepository,
		AssignmentRepository $assignmentRepository,
		RepeatedAssignmentRepository $repeatedAssignmentRepository,
	) {
		$this->personRepository = $personRepository;
		$this->assignmentRepository = $assignmentRepository;
		$this->repeatedAssignmentRepository = $repeatedAssignmentRepository;
	}

	#[Route('/person', name: 'app_person')]
	public function index(Request $request, PersonRepository $personRepository): Response
	{
		$searchTerm = $request->query->get('search', '');

		if (!is_string($searchTerm)) {
			throw new InvalidArgumentException('Search term is not a string');
		}

		$persons = $personRepository->search($searchTerm);

		return $this->render('person/show.html.twig', [
			'persons' => $persons,
			'search_term' => $searchTerm
		]);
	}

	/**
	 * @throws Exception
	 */
	#[Route('/person/{id}', name: 'app_person_detail')]
	public function show(int $id): Response
	{
		$person = $this->personRepository->find($id);

		if (!$person) {
			throw $this->createNotFoundException('No person found for id ' . $id);
		}

		$currentAssignments = $this->assignmentRepository->findCurrentlyOngoing($person);
		$currentRepeatedAssignments = $this->repeatedAssignmentRepository->findCurrentlyOngoing($person);

		if (count($currentAssignments) + count($currentRepeatedAssignments) > 1) {
			throw new LogicException('More assignments for one time for one person, should never be.');
		}

		// Set assignment to 'null' to say no assignment is currently active
		$assignment = false;
		$from = false;
		$to = false;

		if (count($currentAssignments) === 1) {
			$assignment = $currentAssignments[0];
			$from = $assignment->getFromDate()->setTimezone(new DateTimeZone('Europe/Prague'));
			$to = $assignment->getToDate()->setTimezone(new DateTimeZone('Europe/Prague'));
		}
		else if (count($currentRepeatedAssignments) === 1) {
			$assignment = $currentRepeatedAssignments[0];
			$from = $assignment->getFromTime();
			$to = $assignment->getToTime();
		}

		return $this->render('person/detail.html.twig', [
			'person' => $person,
			'currentAssignment' => $assignment,
			'from' => $from ? $from->format('H:i') : $from,
			'to' => $to ? $to->format('H:i') : $to,
		]);
	}
}