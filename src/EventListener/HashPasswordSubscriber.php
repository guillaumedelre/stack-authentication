<?php

namespace App\EventListener;

use App\Entity\Account;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class HashPasswordSubscriber implements EventSubscriber
{
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function prePersist(LifecycleEventArgs $event): void
    {
        /** @var UserInterface $entity */
        $entity = $event->getObject();

        if (!$entity instanceof Account || empty($entity->getPlainPassword())) {
            return;
        }

        $password = $this->passwordEncoder->encodePassword($entity, $entity->getPlainPassword());
        $entity->setPassword($password);
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        /** @var UserInterface $entity */
        $entity = $event->getEntity();

        if (!$entity instanceof Account || empty($entity->getPlainPassword())) {
            return;
        }

        $password = $this->passwordEncoder->encodePassword($entity, $entity->getPlainPassword());
        $entity->setPassword($password);

        $em = $event->getEntityManager();
        $meta = $em->getClassMetadata(Account::class);
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $entity);
    }

    public function getSubscribedEvents()
    {
        return ['prePersist', 'preUpdate'];
    }
}
