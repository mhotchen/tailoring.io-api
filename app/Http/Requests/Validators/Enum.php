<?php
namespace App\Http\Requests\Validators;

use Illuminate\Validation\Validator;
use MyCLabs\Enum\Enum as EnumBaseClass;

final class Enum
{
    /**
     * @param string    $attribute
     * @param mixed     $value
     * @param array     $parameters
     * @param Validator $validator
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validate($attribute, $value, $parameters, Validator $validator): bool
    {
        $class = $parameters[0];
        if (!get_parent_class($class) === EnumBaseClass::class) {
            throw new \InvalidArgumentException("'$class' is not an enum");
        }

        return $class::isValid($value);
    }
}