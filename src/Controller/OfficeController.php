<?php

namespace App\Controller;

use App\Entity\Office;
use App\Repository\OfficeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OfficeController extends AbstractController
{
	public function __construct(private readonly OfficeRepository $officeRepository)
	{
	}

	#[Route('/office/{id}', name: 'app_office_show', methods: ['GET', 'POST'])]
	public function show(Office $office): Response
	{
		$offices = $this->officeRepository->findAll();

		return $this->render('office/show.html.twig', [
			'offices' => $offices,
			'current_office' => $office,
		]);
	}
}
