<?php

namespace Avh\Framework\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class IsJpeg
 *
 * @package Avh\Framework\Validator\Constraints
 */
class IsJpeg extends Constraint
{
    public $message = 'The file is not a valid JPEG.';
}
