<?php

namespace App\Api\Processor\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ProcessorInterface<User, User>
 */
final readonly class UserRegistrationProcessor implements ProcessorInterface
{
    public function __construct(
        /** @var ProcessorInterface<User, User> */
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $decorated,
        private UserPasswordHasherInterface $passwordHasher,
        #[Autowire('%env(APP_SECRET)%')]
        public string $appSecret,
    ) {
    }

    /**
     * @param User $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        $hashedPassword = $this->passwordHasher->hashPassword(
            $data,
            $data->plainPassword
        );

        $data->passwordHash = $hashedPassword;

        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}
