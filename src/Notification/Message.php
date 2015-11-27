<?php

namespace ObjectivePHP\Notification;


class Message extends AbstractMessage
{

    /**
     * Message constructor.
     *
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message);

    }

    /**
     * @param string|null $type
     *
     * @return int
     * @throws \ObjectivePHP\Primitives\Exception
     */
    public function count($type = null)
    {
        $count = 0;
        $this->messages->each(
            function (MessageInterface $message) use (&$count, $type)
            {
                if($type == $message->getType()) $count ++;
            }
        );

        return $count;
    }

}