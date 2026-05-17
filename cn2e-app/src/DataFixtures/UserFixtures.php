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

        $rolesPool = [
            'ROLE_USER',
            'ROLE_CN2E_MEMBER',
            'ROLE_LOCAL_ADMIN',
            'ROLE_CN2E_ADMIN',
            'ROLE_SUPER_ADMIN',
        ];

        for ($i = 1; $i <= 150; $i++) {
            $user = new User();

            $user->setEmail($faker->unique()->safeEmail());
            $user->setRoles([
                $rolesPool[array_rand($rolesPool)]
            ]);
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setProfession(
                $faker->randomElement(Profession::cases())->value
            );
            $user->setProfilePicture('https://picsum.photos/200?random=' . $i);

            $user->setCn2eRole($faker->randomElement($rolesCN2E));

            $user->setLastLoginAt(\DateTimeImmutable::createFromMutable($faker->dateTime()));
            $user->setStatus(
                UserStatus::cases()[array_rand(UserStatus::cases())]
            );
            
            $user->setIsVerified(true);

            $user->setPassword(
                $this->hasher->hashPassword($user, 'test')
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