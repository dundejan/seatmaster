<?php

namespace App\Helper;

use DateTime;
use DateTimeInterface;

class TimeHelper
{
	static function getDateTimeAsString (string|DateTime|DateTimeInterface $dateTime, bool $onlyTime = false) : string
	{
		if (is_string($dateTime)) {
			return $dateTime;
		}
		else if ($onlyTime) {
			return $dateTime->format('H:i:s');
		}
		else {
			return $dateTime->format('Y-m-d H:i:s');
		}
	}
}