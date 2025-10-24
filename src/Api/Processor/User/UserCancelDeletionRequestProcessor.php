<?php

namespace App\Api\Processor\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Enum\UserStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

/**
 * @implements ProcessorInterface<User, User>
 */
readonly class UserCancelDeletionRequestProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @param array<mixed>              $uriVariables
     * @param array{request?: Request,} $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        $user = $data;
        Assert::same($data->status, UserStatus::DeletionRequested);

        $user->status = UserStatus::Active;
        $user->deletionRequestedAt = null;

        $this->em->flush();

        return $user;
    }
}
