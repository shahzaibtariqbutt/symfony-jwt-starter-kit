<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Api\Operation\GetNotFound;
use App\Api\Processor\User\UserCancelDeletionRequestProcessor;
use App\Api\Processor\User\UserRegistrationProcessor;
use App\Api\Processor\User\UserRequestDeletionProcessor;
use App\Enum\UserStatus;
use App\Repository\UserRepository;
use App\Validator;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Webmozart\Assert\Assert as WebmozartAssert;

#[ApiResource(
    operations: [
        new GetNotFound(),
        new Post(
            uriTemplate: '/users/register',
            processor: UserRegistrationProcessor::class,
            validationContext: [
                'groups' => ['Default', 'Valid(User:Register)'],
            ],
            denormalizationContext: [
                'groups' => [
                    'User:W$Register',
                ],
            ],
        ),
        new Post(
            security: '(user.id == object.id) or is_granted("ROLE_ADMIN")',
            uriTemplate: '/users/{id}/request-deletion',
            processor: UserRequestDeletionProcessor::class,
        ),
        new Post(
            security: 'is_granted("ROLE_ADMIN")',
            uriTemplate: '/users/{id}/cancel-deletion-request',
            processor: UserCancelDeletionRequestProcessor::class,
        ),
    ],
)]
#[ORM\Table(name: 'user_account')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: 'email', message: 'This email is already taken.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', length: 36)]
    #[ORM\GeneratedValue('CUSTOM')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    public string $id;

    #[Assert\NotBlank(groups: ['Valid(User:Register)'])]
    #[Assert\Email]
    #[ORM\Column(unique: true)]
    #[Serializer\Groups([
        'User:W$Register',
    ])]
    public string $email {
        get => $this->email;
        set => $this->email = strtolower($value);
    }

    #[Validator\PasswordRequirements(
        groups: ['Valid(User:Register)'],
    )]
    #[Serializer\Groups([
        'User:W$Register',
    ])]
    public string $plainPassword;

    #[ORM\Column]
    public string $passwordHash;

    #[ORM\Column(enumType: UserStatus::class, length: 50, options: ['default' => UserStatus::Active])]
    public UserStatus $status = UserStatus::Active;

    #[Assert\NotBlank]
    #[ORM\Column]
    #[Serializer\Groups([
        'User:W$Register',
    ])]
    public string $name;

    #[ORM\Column(type: 'boolean')]
    public bool $isAdmin = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Serializer\Groups([
        'User:W$Register',
    ])]
    public \DateTimeImmutable $registeredAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Serializer\Groups([
        'UserStats:V',
    ])]
    public ?\DateTimeImmutable $deletionRequestedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Serializer\Groups([
        'UserStats:V',
    ])]
    public ?\DateTimeImmutable $deletionPerformedAt = null;

    public function __construct()
    {
        $this->registeredAt = new DatePoint();
    }

    #[\Override]
    public function eraseCredentials(): void
    {
    }

    #[\Override]
    public function getRoles(): array
    {
        if ($this->isAdmin) {
            return ['ROLE_ADMIN'];
        }

        return ['ROLE_USER'];
    }

    #[\Override]
    public function getUserIdentifier(): string
    {
        WebmozartAssert::stringNotEmpty($this->email);

        return $this->email;
    }

    #[\Override]
    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function getMimeAddress(): Address
    {
        return new Address($this->email, $this->name);
    }

    /**
     * Anonymize all PII data while preserving the user record for audit purposes.
     */
    public function anonymise(): void
    {
        $this->email = sprintf('deleted_%s@sustain.health', $this->id);
        $this->name = 'Deleted User';
    }
}
