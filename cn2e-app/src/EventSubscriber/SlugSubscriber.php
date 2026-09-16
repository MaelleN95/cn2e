<?php

namespace App\EventSubscriber;

use App\Entity\SluggableInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Calcule un identifiant à partir du champ source de l'entité et garantit son unicité parmi toutes les lignes de la même classe d'entité.
 */
#[AsDoctrineListener(event: Events::onFlush)]
class SlugSubscriber
{
    // onFlush s'exécute AVANT le calcul du changeset : on peut donc modifier le slug ici et laisser Doctrine le prendre en compte, même si aucun autre champ de l'entité (titre, nom...) n'a changé.
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        $entities = [
            ...$uow->getScheduledEntityInsertions(),
            ...$uow->getScheduledEntityUpdates(),
        ];

        foreach ($entities as $entity) {
            if (!$entity instanceof SluggableInterface) {
                continue;
            }

            $entity->setSlug($this->generateUniqueSlug($entity, $em));

            // Recalcule le changeset de cette entité pour que le nouveau slug soit bien inclus dans le SQL généré.
            $uow->recomputeSingleEntityChangeSet($em->getClassMetadata($entity::class), $entity);
        }
    }

    private function generateUniqueSlug(SluggableInterface $entity, ObjectManager $em): string
    {
        // Étape 1 : on transforme la source (titre, nom...) en slug "brut" (minuscules, sans accents/espaces).
        $baseSlug = (new AsciiSlugger())
            ->slug((string) $entity->getSlugSource())
            ->lower()
            ->toString();

        // On récupère le repository de la même classe que l'entité (Article, Event, Establishment...).
        $repository = $em->getRepository($entity::class);

        // Étape 2 : tant que ce slug est déjà pris par une autre ligne, on ajoute un suffixe -2, -3, etc.
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->slugTakenByAnotherEntity($repository, $slug, $entity)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    // Vérifie si le slug est déjà utilisé par une AUTRE entité (une même entité peut garder son propre slug).
    private function slugTakenByAnotherEntity(object $repository, string $slug, SluggableInterface $entity): bool
    {
        $existing = $repository->findOneBy(['slug' => $slug]);

        if ($existing === null) {
            return false;
        }

        if (!$existing instanceof SluggableInterface) {
            return true;
        }

        // Si l'id trouvé correspond à l'entité en cours (cas d'une mise à jour), ce n'est pas un conflit.
        return $existing->getId() !== $entity->getId();
    }
}
