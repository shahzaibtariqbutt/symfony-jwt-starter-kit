<?php

namespace App\DataFixtures;

use App\Doctrine\IdGeneratorRemover;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const string ID_ADMIN = '123e4567-e89b-12d3-a456-426614174000';
    public const string ID_TIM = '123e4567-e89b-12d3-a456-426614174001';
    public const string ID_TOM = '123e4567-e89b-12d3-a456-426614174002';
    public const string REF_ADMIN = 'USER.REF_ADMIN';
    public const string REF_TIM = 'USER.REF_TIM';
    public const string REF_TOM = 'USER.REF_TOM';

    private ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;
        IdGeneratorRemover::remove($manager, User::class);

        $this->addReference(self::REF_ADMIN, $this->createUser(
            id: self::ID_ADMIN,
            email: 'admin@example.com',
            name: 'admin',
            isAdmin: true,
            registeredAt: new \DateTimeImmutable('2025-06-09 10:50:30')
        ));

        $this->addReference(self::REF_TIM, $this->createUser(
            id: self::ID_TIM,
            email: 'tim@example.com',
            name: 'tim',
            isAdmin: false,
            registeredAt: new \DateTimeImmutable('2025-06-09 10:50:40')
        ));

        $this->addReference(self::REF_TOM, $this->createUser(
            id: self::ID_TOM,
            email: 'tom@example.com',
            name: 'tom',
            isAdmin: false,
            registeredAt: new \DateTimeImmutable('2025-06-09 10:50:50')
        ));

        $manager->flush();
    }

    public function createUser(
        string $id,
        string $email,
        string $name,
        bool $isAdmin,
        \DateTimeImmutable $registeredAt,
    ): User {
        $user = new User();
        $user->id = $id;
        $user->email = $email;
        $user->name = $name;
        $user->isAdmin = $isAdmin;
        $user->passwordHash = '$2y$13$sP.DNMyfYnNvFTiu5EnB7OstCA6ScY4HIg46xMriPNzk6ZS1bMt.K'; // Test123!
        $user->registeredAt = $registeredAt;

        $this->em->persist($user);

        return $user;
    }
}
