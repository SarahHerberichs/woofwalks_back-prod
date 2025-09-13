<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Channel;

class ChannelFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $channels = ['email', 'sms'];

        foreach ($channels as $channelLabel) {
            $existing = $manager->getRepository(Channel::class)->findOneBy(['label' => $channelLabel]);
            if (!$existing) {
                $channel = new Channel();
                $channel->setLabel($channelLabel);
                $manager->persist($channel);
            }
        }

        $manager->flush();
    }
}
