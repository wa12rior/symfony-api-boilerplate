<?php

namespace App\Core\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Core\Repository\MovieRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Gedmo\Blameable\Blameable;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['address:view']]),
    ],
)]
#[Entity(repositoryClass: MovieRepository::class)]
class Movie implements Blameable, Timestampable
{
    use BlameableEntity;
    use TimestampableEntity;

    #[Id]
    #[Column(type: UuidType::NAME, unique: true)]
    #[Groups(['address:view'])]
    private ?Uuid $id;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }
}
