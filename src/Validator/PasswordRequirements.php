<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

#[\Attribute]
class PasswordRequirements extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\Length(['min' => 8]),
            // Ensure at least one lowercase letter is present.
            new Assert\Regex(
                pattern: '/[a-z]/',
                message: 'Password must contain at least one lowercase letter.',
            ),
            // Ensure at least one uppercase letter is present.
            new Assert\Regex(
                pattern: '/[A-Z]/',
                message: 'Password must contain at least one uppercase letter.',
            ),
            // Must include non-alphanumeric char. (at least one special character)
            new Assert\Regex(
                pattern: '/[^A-Za-z0-9]/',
                message: 'Password must contain at least one special character.',
            ),
        ];
    }
}
