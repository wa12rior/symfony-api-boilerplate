<?php

namespace App\Core\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Core\Controller\MoviesRecommendationAction;
use App\Core\DTO\Request\RecommendationRequest;
use App\Core\Repository\MovieRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Gedmo\Blameable\Blameable;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new GetCollection(
            controller: MoviesRecommendationAction::class,
            input: RecommendationRequest::class,
        )
    ],
)]
#[Entity(repositoryClass: MovieRepository::class)]
class Movie implements Blameable, Timestampable
{
    use BlameableEntity;
    use TimestampableEntity;

    #[Id]
    #[Column(type: UuidType::NAME, unique: true)]
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
