<?php

namespace ObjectivePHP\Notification;


use ObjectivePHP\Primitives\Collection\Collection;

class Stack extends Collection
{

    public function __construct($messages = [])
    {
        parent::__construct($messages);
        $this->restrictTo(MessageInterface::class);

    }

    /**
     * @param string            $key
     * @param MessageInterface  $message
     *
     * @throws \ObjectivePHP\Primitives\Exception
     */
    public function addMessage($key, $message)
    {
        $this->set($key, $message);
    }

    /**
     * @param string|null $type
     *
     * @return int
     * @throws \ObjectivePHP\Primitives\Exception
     */
    public function count($type = null)
    {
        if(is_null($type))
        {
            $count = parent::count();
        }
        else
        {
            $count = 0;
            $this->message->each(
                function (MessageInterface $message) use (&$count, $type)
                {
                    if($type == $message->getType()) $count ++;
                }
            );
        }

        return $count;
    }

}