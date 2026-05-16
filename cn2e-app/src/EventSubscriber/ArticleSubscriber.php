<?php

namespace App\EventSubscriber;

use App\Entity\Article;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
class ArticleSubscriber
{
    public function __construct(
        private Security $security
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Article) {
            return;
        }

        if ($entity->getAuthor() !== null) {
            return;
        }

        $user = $this->security->getUser();

        if ($user) {
            $entity->setAuthor($user);
        }
    }
}