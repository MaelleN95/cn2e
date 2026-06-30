<?php

namespace App\DataFixtures;

use App\Entity\Establishment;
use App\Enum\EstablishmentAcademy;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EstablishmentFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $establishmentTypes = ['EREA', 'LEA', 'ERPD'];

        for ($i = 1; $i <= 100; $i++) {
            $e = new Establishment();

            $type = $faker->randomElement($establishmentTypes);
            $e->setName($type . ' ' . $faker->city());
            $e->setSlug($faker->slug() . '-' . random_int(1, 9999));
            $e->setCity($faker->city());
            $e->setDepartment($faker->departmentNumber());
            $e->setRegion($faker->region());
            $e->setAcademy($faker->randomElement(EstablishmentAcademy::values()));
            $e->setAddress($faker->address());
            $e->setAddressHash(md5($e->getAddress()));
            $e->setLatitude($faker->latitude(42.0, 50.8));
            $e->setLongitude($faker->longitude(-4.8, 8.5));
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