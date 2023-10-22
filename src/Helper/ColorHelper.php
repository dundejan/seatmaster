<?php

namespace App\Helper;

class ColorHelper
{
	/**
	 * @return string[]
	 */
	public static function getNiceColors(): array {
		return [
			'rgb(255, 99, 132)',  // pink
			'rgb(75, 192, 192)',  // teal
			'rgb(255, 159, 64)',  // orange
			'rgb(153, 102, 255)', // purple
			'rgb(54, 162, 235)',  // blue
			'rgb(255, 206, 86)',  // yellow
			'rgb(231, 76, 60)',   // red
			'rgb(46, 204, 113)',  // green
			'rgb(52, 152, 219)',  // light blue
			'rgb(155, 89, 182)',  // lavender
			'rgb(241, 196, 15)',  // sunflower yellow
			'rgb(26, 188, 156)',  // turquoise
			'rgb(22, 160, 133)',  // green sea
			'rgb(52, 73, 94)',    // wet asphalt
			'rgb(192, 57, 43)',   // alizarin
			'rgb(189, 195, 199)', // gray
			'rgb(243, 156, 18)',  // orange
			'rgb(142, 68, 173)',  // wisteria
			'rgb(44, 62, 80)',    // midnight blue
			'rgb(211, 84, 0)',    // pumpkin orange
		];
	}
}