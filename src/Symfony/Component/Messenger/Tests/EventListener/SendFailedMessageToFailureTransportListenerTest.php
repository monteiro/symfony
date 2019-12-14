<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\EventListener\SendFailedMessageToFailureTransportListener;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;

class SendFailedMessageToFailureTransportListenerTest extends TestCase
{
    public function testDoNothingIfFailureTransportIsNotDefined()
    {
        $sender = $this->createMock(SenderInterface::class);
        $sender->expects($this->never())->method('send');

        $listener = new SendFailedMessageToFailureTransportListener(null);

        $exception = new \Exception('no!');
        $envelope = new Envelope(new \stdClass());
        $event = new WorkerMessageFailedEvent($envelope, 'my_receiver', $exception);

        $listener->onMessageFailed($event);
    }

    public function testItSendsToTheFailureTransport()
    {
        $sender = $this->createMock(SenderInterface::class);
        $sender->expects($this->once())->method('send')->with($this->callback(function ($envelope) {
            /* @var Envelope $envelope */
            $this->assertInstanceOf(Envelope::class, $envelope);

            /** @var SentToFailureTransportStamp $sentToFailureTransportStamp */
            $sentToFailureTransportStamp = $envelope->last(SentToFailureTransportStamp::class);
            $this->assertNotNull($sentToFailureTransportStamp);
            $this->assertSame('my_receiver', $sentToFailureTransportStamp->getOriginalReceiverName());

            return true;
        }))->willReturnArgument(0);
        $listener = new SendFailedMessageToFailureTransportListener($sender);

        $exception = new \Exception('no!');
        $envelope = new Envelope(new \stdClass());
        $event = new WorkerMessageFailedEvent($envelope, 'my_receiver', $exception);

        $listener->onMessageFailed($event);
    }

    public function testDoNothingOnRetry()
    {
        $sender = $this->createMock(SenderInterface::class);
        $sender->expects($this->never())->method('send');
        $listener = new SendFailedMessageToFailureTransportListener($sender);

        $envelope = new Envelope(new \stdClass());
        $event = new WorkerMessageFailedEvent($envelope, 'my_receiver', new \Exception());
        $event->setForRetry();

        $listener->onMessageFailed($event);
    }

    public function testDoNotRedeliverToFailed()
    {
        $sender = $this->createMock(SenderInterface::class);
        $sender->expects($this->never())->method('send');
        $listener = new SendFailedMessageToFailureTransportListener($sender);

        $envelope = new Envelope(new \stdClass(), [
            new SentToFailureTransportStamp('my_receiver'),
        ]);
        $event = new WorkerMessageFailedEvent($envelope, 'my_receiver', new \Exception());

        $listener->onMessageFailed($event);
    }
}
