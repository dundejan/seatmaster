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
	public string $message = 'Collision! This assignment has collision with {{ assignmentType }} assignment and uses same {{ param }}.';
	public function getTargets(): array|string
	{
		return self::CLASS_CONSTRAINT;
	}
}
