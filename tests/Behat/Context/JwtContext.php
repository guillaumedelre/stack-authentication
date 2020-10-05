<?php

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class JwtContext implements Context
{
    private JWTTokenManagerInterface $tokenManager;
    private $userProvider;

    public function __construct(JWTTokenManagerInterface $tokenManager, \Traversable $userProviders)
    {
        $this->tokenManager = $tokenManager;
        $this->userProvider = current(iterator_to_array($userProviders));
    }

    /**
     * @Given a bearer is generated for :email
     */
    public function aBearerIsGeneratedFor(string $email)
    {
        return $this->tokenManager->create($this->userProvider->loadUserByUsername($email));
    }

}
