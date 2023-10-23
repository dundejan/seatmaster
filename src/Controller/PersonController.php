<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PersonRepository;

class PersonController extends AbstractController
{
	#[Route('/person', name: 'app_person')]
	public function index(Request $request, PersonRepository $personRepository): Response
	{
		$searchTerm = $request->query->get('search', '');

		$persons = $personRepository->search($searchTerm);

		return $this->render('person/show.html.twig', [
			'persons' => $persons,
			'search_term' => $searchTerm
		]);
	}
}