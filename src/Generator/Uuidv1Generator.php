<?php

namespace App\Generator;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Ramsey\Uuid\Uuid;

class Uuidv1Generator extends AbstractIdGenerator
{
    /**
     * @param EntityManager $em
     * @param Entity $entity
     * @return string
     */
    public function generate(EntityManager $em, $entity)
    {
        return Uuid::uuid1();
    }
}
