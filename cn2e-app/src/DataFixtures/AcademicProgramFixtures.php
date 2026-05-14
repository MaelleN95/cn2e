<?php

namespace App\DataFixtures;

use App\Entity\AcademicProgram;
use App\Entity\Establishment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AcademicProgramFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $levels = ['CAP', 'Bac Pro', 'BTS'];

        $capTitles = [
            'CAP Cuisine',
            'CAP Électrotechnique',
            'CAP Menuiserie',
            'CAP Plomberie',
            'CAP Comptabilité',
            'CAP Vente',
            'CAP Maintenance des véhicules',
            'CAP Coiffure',
            'CAP Boulangerie-Pâtisserie',
            'CAP Ébénisterie'
        ];

        $bacProTitles = [
            'Bac Pro Commerce',
            'Bac Pro Électrotechnique',
            'Bac Pro Informatique',
            'Bac Pro Logistique',
            'Bac Pro Gestion-Administration',
            'Bac Pro Métiers de l\'esthétique',
            'Bac Pro Cuisine',
            'Bac Pro Maintenance des équipements industriels',
            'Bac Pro Transport',
            'Bac Pro Services aux personnes'
        ];

        $btsTitles = [
            'BTS Comptabilité et Gestion',
            'BTS Informatique',
            'BTS Électrotechnique',
            'BTS Commerce International',
            'BTS Gestion de PME',
            'BTS Développement Durable',
            'BTS Services Informatiques aux Organisations',
            'BTS Assistance Technique d\'Ingénieur',
            'BTS Design Graphique',
            'BTS Tourisme'
        ];

        for ($i = 1; $i <= 20; $i++) {
            $p = new AcademicProgram();

            $level = $faker->randomElement($levels);
            $p->setLevel($level);

            switch ($level) {
                case 'CAP':
                    $p->setTitle($faker->randomElement($capTitles));
                    break;
                case 'Bac Pro':
                    $p->setTitle($faker->randomElement($bacProTitles));
                    break;
                case 'BTS':
                    $p->setTitle($faker->randomElement($btsTitles));
                    break;
            }

            $establishmentCount = rand(0, 12);

            $usedIds = [];

            for ($j = 0; $j < $establishmentCount; $j++) {
                do {
                    $id = rand(1, 100);
                } while (in_array($id, $usedIds, true));

                $usedIds[] = $id;

                $establishment = $this->getReference(
                    'establishment_' . $id,
                    Establishment::class
                );

                $p->addEstablishment($establishment);
            }

            $manager->persist($p);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [EstablishmentFixtures::class];
    }
}