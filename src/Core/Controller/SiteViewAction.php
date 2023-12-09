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
        private UserRepository      $userRepository,
        private NormalizerInterface $itemNormalizer,
    ) {
    }

    public function __invoke(Site $data, Request $request): JsonResponse
    {
        if ($data->getState() === SiteState::UNPUBLISHED) {
            return $this->json(['message' => 'Site not found'], status: JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json([
            'site' => $this->itemNormalizer->normalize($data, context: ['groups' => 'site_public:view']),
            'resource' => $this->itemNormalizer->normalize($data->getWorkflow(), context: ['groups' => 'workflow_preview:view']),
            'author' => $this->itemNormalizer->normalize($this->userRepository->find($data->getCreatedBy()), context: ['groups' => 'user_public:view']),
        ]);
    }
}
