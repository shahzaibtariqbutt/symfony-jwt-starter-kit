<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Entity\User;
use App\Enum\UserStatus;
use App\Tests\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Webmozart\Assert\Assert;

final class UserDeletionPerformCommandTest extends BaseTestCase
{
    use ClockSensitiveTrait;
    use MailerAssertionsTrait;

    public function testCommandPerformsDeletionAfterCoolingOffPeriod(): void
    {
        // Mock time to a fixed date
        self::mockTime(new \DateTimeImmutable('2024-10-10 00:00:00'));
        $this->registerUser('john@doe.com');

        // Login and request deletion
        $id = $this->logIn('john@doe.com')->id;

        $this->request('POST', "/users/$id/request-deletion", []);
        $this->assertResponseIsSuccessful();

        // Verify deletion was requested but not performed
        $user = $this->getUser(id: $id);
        $this->assertNull($user->deletionPerformedAt);
        $this->assertNotNull($user->deletionRequestedAt);

        // Move time forward past a cooling-off period (14+ days)
        self::mockTime(new \DateTimeImmutable('2024-10-25 00:00:00'));

        // Run the deletion command
        $commandTester = $this->getCommandTester('app:users:deletion:perform');
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
        // Verify that this command sent one email
        $this->assertEmailCount(1);

        // Verify user was anonymized and deletion performed
        $user = $this->getUser(id: $id);
        $this->assertNotNull($user->deletionPerformedAt);
        $this->assertStringStartsWith('deleted_', $user->email);
        $this->assertSame('Deleted User', $user->name);
    }

    public function testCommandDoesNotDeleteUsersWithinCoolingOffPeriod(): void
    {
        // Mock time to a fixed date
        self::mockTime(new \DateTimeImmutable('2024-10-10 00:00:00'));
        $this->registerUser('john@doe.com');

        // Login and request deletion
        $userId = $this->logIn('john@doe.com')->id;

        $this->request('POST', "/users/$userId/request-deletion", []);
        $this->assertResponseIsSuccessful();

        $user = $this->getUser('john@doe.com');
        $this->assertNotNull($user->deletionRequestedAt);
        $this->assertSame($user->status, UserStatus::DeletionRequested);

        // Move time forward but stay within cooling-off period (10 days)
        self::mockTime(new \DateTimeImmutable('2024-10-20 00:00:00'));

        // Run the deletion command
        $commandTester = $this->getCommandTester('app:users:deletion:perform');
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();

        // Verify user was NOT deleted
        Assert::notNull($user = $this->getEm()->getRepository(User::class)->find($userId));
        $this->assertNull($user->deletionPerformedAt);
        $this->assertStringContainsString('No users found for deletion', $commandTester->getDisplay());
    }
}
