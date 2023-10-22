<?php

namespace App\Helper;

use Symfony\UX\Chartjs\Model\Chart;

class ChartHelper
{
	public static function addPluginZoom(Chart $chart) : Chart
	{
		$existingOptions = $chart->getOptions();
		$existingOptions['plugins']['zoom'] = [
			'zoom' => [
				'wheel' => ['enabled' => true],
				'pinch' => ['enabled' => true],
				'mode' => 'xy',
			],
			'pan' => [
				'enabled' => true,
				'mode' => 'xy',
				'threshold' => 10,
			],
		];

		$chart->setOptions($existingOptions);

		return $chart;
	}

	public static function addPluginAnnotation(Chart $chart, string $verticalLine) : void
	{
		$existingOptions = $chart->getOptions();
		$existingOptions['plugins']['annotation'] = [
			'annotations' => [
				[
					'type' => 'line',
					'mode' => 'vertical',
					'scaleID' => 'x',
					'value' => $verticalLine,
					'borderColor' => 'rgb(217, 120, 23)',
					'borderWidth' => 1.5,
					'label' => [
						'enabled' => true,
						'content' => 'Current hour'
					]
				],
			],
		];

		$chart->setOptions($existingOptions);
	}
}