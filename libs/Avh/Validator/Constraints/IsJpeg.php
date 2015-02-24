<?php

namespace Avh\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class IsJpeg extends Constraint
{
    public $message = 'The file is not a valid JPEG.';
}

