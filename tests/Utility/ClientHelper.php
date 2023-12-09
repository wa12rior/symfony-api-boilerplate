<?php

declare(strict_types=1);

namespace App\Tests\Utility;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;

trait ClientHelper
{
    /**
     * @param mixed[] $headers
     */
    private function getAuthenticatedClient(string $email = 'john.doe@example.com', array $headers = []): Client
    {
        if (!is_subclass_of(self::class, ApiTestCase::class)) {
            throw new \RuntimeException('This is not application test.');
        }

        $defaultArray = [
            'Content-Type' => 'application/ld+json',
            'email' => $email,
        ];

        $client = static::createClient(defaultOptions: [
            'headers' => array_merge($defaultArray, $headers)
        ]);
//        $userRepository = static::getContainer()->get(UserRepository::class);
//
//        // retrieve the test user
//        $testUser = $userRepository->find(UserFixtures::USER_ID);
//
//        // simulate $testUser being logged in
//        $client->loginUser($testUser);

        return $client;
    }
}
