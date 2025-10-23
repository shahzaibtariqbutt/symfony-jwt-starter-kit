<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Attribute\InjectUser;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;

class DecoratingNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    /** @var NormalizerInterface&DenormalizerInterface&SerializerAwareInterface */
    private SerializerAwareInterface $decorated;

    public function __construct(
        SerializerAwareInterface $decorated,
        private readonly Security $security,
    ) {
        Assert::implementsInterface($decorated, DenormalizerInterface::class);
        Assert::implementsInterface($decorated, NormalizerInterface::class);
        $this->decorated = $decorated;
    }

    /**
     * @return array<class-string|'*'|'object'|string, bool|null>
     */
    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            'object' => true,
        ];
    }

    /**
     * @param mixed[] $context
     */
    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = $this->decorated->denormalize($data, $type, $format, $context);

        // @phpstan-ignore-next-line
        $className = $context['input']['class'] ?? $type;
        Assert::classExists($className);
        $reflectionClass = new \ReflectionClass($className);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            foreach ($reflectionProperty->getAttributes() as $attribute) {
                $attributeClass = $attribute->getName();
                Assert::classExists($attributeClass);
                switch ($attributeClass) {
                    case InjectUser::class:
                        $user = $this->security->getUser();
                        if ($user instanceof User) {
                            $prop = $reflectionProperty->name;
                            if (!isset($object->{$prop})) {
                                $object->{$prop} = $user;
                            }
                        }
                        break;
                }
            }
        }

        return $object;
    }

    /**
     * @param mixed[] $context
     */
    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }

    /**
     * @param mixed[] $context
     */
    #[\Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): string|int|bool|\ArrayObject|array|float|null
    {
        return $this->decorated->normalize($object, $format, $context);
    }

    /**
     * @param mixed[] $context
     */
    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    #[\Override]
    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->decorated->setSerializer($serializer);
    }
}
