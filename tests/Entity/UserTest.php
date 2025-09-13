<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Walk;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {


    public function testEmailGetterSetter(): void {
        $user = new User();
        $user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testUsernameGetterSetter(): void {
        $user = new User();
        $user->setUsername('JohnDoe');
        $this->assertSame('JohnDoe', $user->getUsername());
    }

    public function testPasswordGetterSetter(): void {
        $user = new User();
        $user->setPassword('hashedpassword');
        $this->assertSame('hashedpassword', $user->getPassword());
        
        $user->setPlainPassword('plainpassword');
        $this->assertSame('plainpassword', $user->getPlainPassword());
    }

    public function testRoles(): void {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);
        $roles = $user->getRoles();
        $this->assertContains('ROLE_USER', $roles); // ROLE_USER ajouté par défaut
        $this->assertContains('ROLE_ADMIN', $roles);
    }

    public function testCreatedWalks(): void {
        $user = new User();
        $walk = new Walk();

        $user->addCreatedWalk($walk);
        $this->assertCount(1, $user->getCreatedWalks());
        $this->assertSame($user, $walk->getCreator());

        $user->removeCreatedWalk($walk);
        $this->assertCount(0, $user->getCreatedWalks());
        $this->assertNull($walk->getCreator());
    }
    public function testParticipatedWalks(): void {
        $user = new User();
        $walk = new Walk();

        $user->addParticipatedWalk($walk);
        $this->assertCount(1, $user->getParticipatedWalks());
        $this->assertTrue($walk->getParticipants()->contains($user));

        $user->removeParticipatedWalk($walk);
        $this->assertCount(0, $user->getParticipatedWalks());
        $this->assertFalse($walk->getParticipants()->contains($user));
    }


}
