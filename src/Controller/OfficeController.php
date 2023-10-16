<?php

namespace App\Controller;

use App\Entity\Office;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OfficeController extends AbstractController
{
	#[Route('/office/{id}', name: 'app_office_show', methods: ['GET', 'POST'])]
	public function show(Office $office): Response
	{
		$seats = $office->getSeats();

		return $this->render('office/show.html.twig', [
			'current_office' => $office,
			'seats' => $seats,
		]);
	}
}
