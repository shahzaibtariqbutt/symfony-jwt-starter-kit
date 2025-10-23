<?php

declare(strict_types=1);

namespace App\Api\Operation;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Symfony\Action\NotFoundAction;

/**
 * GET operation required to generate an IRI, but sometimes we don't want
 * individual resources to be accessible.
 */
final class GetNotFound extends HttpOperation
{
    public function __construct()
    {
        parent::__construct(
            self::METHOD_GET,
            controller: NotFoundAction::class,
            read: false,
            status: 404
        );
    }
}
