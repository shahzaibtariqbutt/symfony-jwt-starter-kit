<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Enum\UserStatus;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    /**
     * Check for conditions which mean the user should be prevented from logging in.
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (UserStatus::Active !== $user->status) {
            throw new CustomUserMessageAccountStatusException($user->status->value);
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
