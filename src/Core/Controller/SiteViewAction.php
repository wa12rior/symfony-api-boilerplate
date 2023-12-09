<?php

declare(strict_types=1);

namespace App\Core\Controller;

use App\Auth\Repository\UserRepository;
use App\Core\Entity\Site;
use App\Core\Enum\SiteState;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsController]
class SiteViewAction extends AbstractController
{
    public function __construct(
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return $this->json([]);
    }
}
