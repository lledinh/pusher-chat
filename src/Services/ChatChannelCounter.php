<?php

namespace App\Services;

use App\Entity\Channel;
use Doctrine\ORM\EntityManagerInterface;

class ChatChannelCounter {
    private $channels;
    private $channelsCount;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        $this->channels = $this->em
            ->getRepository(Channel::class)
            ->findAll();

        foreach ($this->channels as $channel) {
            $this->channelsCount[$channel->getId()] = 0;
        }
    }

    public function getDefaultsChannels() {
        return $this->channels;
    }

    public function getChannelsCounts() {
        return $this->channelsCount;
    }


    public function getCount($channelName) {

    }

    public function add($channelName) {

    }

    public function sub($channelName) {

    }
}