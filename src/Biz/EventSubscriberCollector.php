<?php

namespace Biz;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriberCollector
{
    protected $subscribers;

    public function __construct()
    {
        $this->subscribers = array();
    }

    public function add(EventSubscriberInterface $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }

    public function all()
    {
        return $this->subscribers;
    }
}
