<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Walk;
use PHPUnit\Framework\TestCase;

class WalkTest extends TestCase {

    public function testWalkCreation(): void {
        
        $walk = new Walk();

        $this->assertInstanceOf(Walk::class, $walk);
        $this->assertNotNull($walk->getCreatedAt());
        $this->assertNotNull($walk->getUpdatedAt());
        $this->assertTrue($walk->getParticipants()->isEmpty());
        $this->assertTrue($walk->getIsCustomLocation());
    }

    public function testSettersAndGetters(): void {
        $walk = new Walk();
        $title = 'Morning Walk';
        $description = 'A nice walk in the park';
        $date = new \DateTimeImmutable('2025-08-15 10:00:00');
        $maxParticipants = 10;

        $walk->setTitle($title)
             ->setDescription($description)
             ->setDate($date)
             ->setMaxParticipants($maxParticipants);

        $this->assertSame($title, $walk->getTitle());
        $this->assertSame($description, $walk->getDescription());
        $this->assertEquals($date, $walk->getDate());
        $this->assertSame($maxParticipants, $walk->getMaxParticipants());
    }

    public function testParticipants(): void {
        $walk = new Walk();
        $user = new User();

        $walk->addParticipant($user);
        $this->assertCount(1, $walk->getParticipants());
        $this->assertSame($walk, $user->getParticipatedWalks()->first());

        $walk->removeParticipant($user);
        $this->assertCount(0, $walk->getParticipants());
    }
}
