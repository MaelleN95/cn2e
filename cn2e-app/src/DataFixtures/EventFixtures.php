<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;



class EventFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $categories = ['Formation', 'Conférence', 'Atelier', 'Journée portes ouvertes', 'Assemblée générale'];

        $eventTitles = [
            'Journée Portes Ouvertes',
            'Formation Continue en Pédagogie',
            'Conférence sur l\'Inclusion Scolaire',
            'Atelier Méthodes Innovantes',
            'Assemblée Générale Annuelle',
            'Séminaire sur l\'Orientation Professionnelle',
            'Rencontre des Établissements Adaptés',
            'Formation aux Nouvelles Technologies',
            'Événement Sportif Inter-Établissements',
            'Journée d\'Échange Pédagogique'
        ];

        for ($i = 1; $i <= 15; $i++) {
            $e = new Event();

            $start = $faker->dateTimeBetween('-6 months', '+6 months');
            $end = (clone $start)->modify('+'.rand(1, 3).' days');

            $e->setTitle($faker->randomElement($eventTitles));
            $e->setSlug($faker->slug());
            $e->setStartDate(\DateTimeImmutable::createFromMutable($start));
            $e->setEndDate(\DateTimeImmutable::createFromMutable($end));
            $e->setLocation($faker->city());
            $e->setTime($faker->time());
            $e->setShortDescription($faker->paragraph());
            $e->setContent($faker->text(800));
            $e->setImage('https://picsum.photos/400?random=' . $i);
            $e->setCategory($faker->randomElement($categories));
            $e->setIsMembersOnly($faker->boolean());
            $e->setHasRegistration($faker->boolean());

            $manager->persist($e);
        }

        $manager->flush();
    }
}