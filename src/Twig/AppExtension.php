<?php

namespace App\Twig;

use App\Entity\Office;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Repository\OfficeRepository;

class AppExtension extends AbstractExtension
{
	private OfficeRepository $officeRepository;

	public function __construct(OfficeRepository $officeRepository)
	{
		$this->officeRepository = $officeRepository;
	}

	public function getFunctions(): array
	{
		return [
			new TwigFunction('get_offices', [$this, 'getOffices']),
		];
	}

	/**
	 * @return array<Office>
	 */
	public function getOffices() : array
	{
		return $this->officeRepository->findBy([], ['name' => 'ASC']);
	}
}