<?php
namespace Tests\ObjectivePHP\NotificationMessage;

use ObjectivePHP\Notification;
use ObjectivePHP\PHPUnit\TestCase;


class NotificationMessageTest extends TestCase
{

    public function testNotification()
    {
        $matcher = new Notification\Message('test');
        $this->assertEquals('test', $matcher);
    }

    public function testAddMessage()
    {
        $message = new Notification\Message('hello');
        $messageStack = new Notification\Stack();

        $messageStack->addMessage('data.form', $message);

        $this->assertEquals(['data.form' => $message], $messageStack->toArray());
    }

}
