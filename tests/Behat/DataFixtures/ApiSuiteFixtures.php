<?php

namespace App\Tests\Behat\DataFixtures;

use App\Entity\Account;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ApiSuiteFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $object = (new Account())
            ->setEmail('test@stack.local')
            ->setFullname('Test fullname')
            ->setRoles(['ROLES_USER'])
            ->setPlainPassword('test');
        $manager->persist($object);

        $object = (new Account())
            ->setEmail('admin@stack.local')
            ->setFullname('Admin fullname')
            ->setRoles(['ROLE_ADMIN'])
            ->setPlainPassword('admin');
        $manager->persist($object);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['api_accounts'];
    }

}
