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
	public string $message = 'This assignment has {{ numberOfCollisions }} {{ collisionType }} collision with {{ assignmentType }} assignment. 
	One of the colliding {{ assignmentType }} assignments: [{{ collidingAssignment }}].';
	public function getTargets(): array|string
	{
		return self::CLASS_CONSTRAINT;
	}
}
