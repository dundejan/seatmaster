<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class IsAvailableAssignment extends Constraint
{
	public string $message = 'There is already assignment in this time period for selected {{ value }}.';
	public function getTargets(): array|string
	{
		return self::CLASS_CONSTRAINT;
	}
}
