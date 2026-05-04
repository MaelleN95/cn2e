<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;



class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $categories = ['Actualités', 'Événements', 'Conseils pédagogiques', 'Témoignages', 'Ressources éducatives'];

        $titlePrefixes = [
            'Les nouvelles méthodes en ',
            'Préparation au ',
            'L\'importance de ',
            'Témoignage : ',
            'Guide pour ',
            'Actualité : ',
            'Événement : ',
            'Conseil : ',
            'Ressource : ',
            'Focus sur '
        ];

        $titleSubjects = [
            'enseignement adapté',
            'baccalauréat',
            'orientation professionnelle',
            'inclusion scolaire',
            'formation continue',
            'pédagogie moderne',
            'soutien aux élèves',
            'technologie en classe',
            'projets éducatifs',
            'vie associative'
        ];

        for ($i = 1; $i <= 40; $i++) {
            $a = new Article();

            $a->setTitle($faker->randomElement($titlePrefixes) . $faker->randomElement($titleSubjects));
            $a->setSlug($faker->slug());
            if ($i <= 20) {
                $a->setPublishedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
            } else {
                $a->setPublishedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-5 years', 'now')));
            }
            $a->setShortDescription($faker->paragraph());
            $a->setContent($faker->text(1000));
            $a->setImage('https://picsum.photos/300?random=' . $i);
            $a->setCategory($faker->randomElement($categories));
            $a->setIsMembersOnly($faker->boolean());

            $a->setAuthor(
                $this->getReference('user_' . rand(1, 30), User::class)
            );

            $manager->persist($a);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}