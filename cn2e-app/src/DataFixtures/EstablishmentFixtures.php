<?php

namespace App\DataFixtures;

use App\Entity\Establishment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EstablishmentFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $establishmentTypes = ['EREA', 'LEA', 'ERPD'];

        for ($i = 1; $i <= 30; $i++) {
            $e = new Establishment();

            $type = $faker->randomElement($establishmentTypes);
            $e->setName($type . ' ' . $faker->city());
            $e->setCity($faker->city());
            $e->setDepartment($faker->departmentNumber());
            $e->setRegion($faker->region());
            $e->setAddress($faker->address());
            $e->setAddressHash(md5($e->getAddress()));
            $e->setLatitude($faker->latitude());
            $e->setLongitude($faker->longitude());
            $e->setPhone($faker->phoneNumber());
            $e->setEmail($faker->companyEmail());
            $e->setWebsite($faker->url());
            $e->setDescription($faker->paragraph() . ' Cet établissement offre des formations adaptées aux besoins spécifiques des élèves.');

            $manager->persist($e);

            $this->addReference("establishment_$i", $e);
        }

        $manager->flush();
    }
}