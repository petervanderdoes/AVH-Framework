<?php

namespace Avh\Validator\Constraints;

use Symfony\Component\HttpFoundation\File\File as FileObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class IsJpegValidator
 *
 * @package Avh\Validator\Constraints
 */
class IsJpegValidator extends ConstraintValidator
{
    /** @var  \Symfony\Component\Validator\Context\ExecutionContextInterface */
    protected $context;

    /**
     * @inheritdoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof isJpeg) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\IsJpeg');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !$value instanceof FileObject && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (!$value instanceof FileObject) {
            $value = new FileObject($value);
        }

        $uploaded_file_name = $value->getRealPath();
        $image_mime_type = exif_imagetype($uploaded_file_name);
        if ($image_mime_type == IMAGETYPE_JPEG) {
            return;
        }
        $this->context->buildViolation($constraint->message)
                      ->addViolation()
        ;
    }
}



