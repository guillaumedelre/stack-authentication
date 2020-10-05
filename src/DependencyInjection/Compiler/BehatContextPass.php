<?php

namespace App\DependencyInjection\Compiler;

use App\Tests\Behat\Context\JwtContext;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BehatContextPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('lexik_jwt_authentication.generate_token_command')) {
            return;
        }

        $jwtContext = (new Definition())
            ->setClass(JwtContext::class)
            ->setPublic(true)
            ->setArgument(0, $container->getDefinition('lexik_jwt_authentication.generate_token_command')->getArgument(0))
            ->setArgument(1, $container->getDefinition('lexik_jwt_authentication.generate_token_command')->getArgument(1));

        $container->setDefinition(JwtContext::class, $jwtContext);
    }
}
