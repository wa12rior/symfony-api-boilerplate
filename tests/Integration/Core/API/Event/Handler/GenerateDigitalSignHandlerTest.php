<?php

declare(strict_types=1);

namespace App\Tests\Integration\Core\API\Event\Handler;

use App\Common\Entity\MediaObject;
use App\Core\API\Event\DocumentSigned;
use App\Core\Entity\Document\DocumentMedia;
use App\Core\Entity\Signature\SignatureCard;
use App\Core\Repository\Document\DocumentMediaRepository;
use App\Core\Repository\Signature\SignatureCardRepository;
use App\Tests\Utility\Factory\Core\DocumentFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class GenerateDigitalSignHandlerTest extends KernelTestCase
{
    protected MessageBusInterface $messageBus;

    protected SignatureCardRepository $signatureCardRepository;
    protected DocumentMediaRepository $documentMediaRepository;
    protected DocumentFactory $documentFactory;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->messageBus = $container->get('event.bus');
        $this->documentMediaRepository = $container->get(DocumentMediaRepository::class);
        $this->signatureCardRepository = $container->get(SignatureCardRepository::class);
        $this->documentFactory = $container->get(DocumentFactory::class);
    }

    public function testShouldGeneratePdf(): void
    {
        $this->markTestIncomplete('Not saving');
        /** @phpstan-ignore-next-line */
        $document = $this->documentFactory->aDocumentWithDigitalSign();

        $event = new DocumentSigned($document->getId()->jsonSerialize());

        $this->messageBus->dispatch($event);

        $card = $this->signatureCardRepository->findOneBy(['document' => $document]);
        $documentMedia = $this->documentMediaRepository->findOneBy(['document' => $document, 'mediaObject' => $card->getCardFile()]);

        $this->assertInstanceOf(SignatureCard::class, $card);
        $this->assertInstanceOf(MediaObject::class, $card->getCardFile());
        $this->assertInstanceOf(DocumentMedia::class, $documentMedia);
    }
}
