<?php

namespace App\Controller;

use App\Repository\OfficeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
	public function __construct(private readonly OfficeRepository $officeRepository)
	{
	}
	#[Route('/', name: 'app_homepage')]
	public function homepage(): Response
	{
		$offices = $this->officeRepository->findAll();

		return $this->render('homepage.html.twig', [
			'offices' => $offices,
		]);
	}
}