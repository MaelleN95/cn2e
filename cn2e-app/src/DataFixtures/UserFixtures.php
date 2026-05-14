<?php

namespace App\DataFixtures;

use App\Entity\Establishment;
use App\Entity\User;
use App\Enum\Profession;
use App\Enum\UserStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $rolesCN2E = ['Président', 'Vice-président', 'Trésorier', 'Secrétaire', 'Membre actif', 'Membre associé'];

        for ($i = 1; $i <= 150; $i++) {
            $user = new User();

            $user->setEmail($faker->unique()->safeEmail());
            $user->setRoles(['ROLE_USER']);
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setProfession(
                $faker->randomElement(Profession::cases())->value
            );
            $user->setProfilePicture('https://picsum.photos/200?random=' . $i);

            $user->setIsCn2eMember(true);
            $user->setCn2eRole($faker->randomElement($rolesCN2E));
            $user->setPhone($faker->phoneNumber());

            $user->setLastLoginAt(\DateTimeImmutable::createFromMutable($faker->dateTime()));
            $user->setStatus(UserStatus::PENDING);
            $user->setIsVerified(true);

            $user->setPassword(
                $this->hasher->hashPassword($user, 'password')
            );

            $user->setEstablishment(
                $this->getReference('establishment_' . rand(1, 10), Establishment::class)
            );

            $manager->persist($user);

            $this->addReference("user_$i", $user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [EstablishmentFixtures::class];
    }
}