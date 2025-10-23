<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\Persistence\ObjectManager;
use Webmozart\Assert\Assert;

final class IdGeneratorRemover
{
    /**
     * @param class-string $class
     */
    public static function remove(ObjectManager $em, string $class): void
    {
        $metadata = $em->getClassMetaData($class);
        Assert::isInstanceOf($metadata, \Doctrine\ORM\Mapping\ClassMetadata::class);
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
    }
}
