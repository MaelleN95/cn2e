<?php

namespace App\DataFixtures;

use App\Entity\Establishment;
use App\Entity\User;
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

        $professions = [
            'Professeur des écoles',
            'Professeur de lycée',
            'Directeur d\'établissement',
            'Éducateur spécialisé',
            'Psychologue scolaire',
            'Conseiller d\'orientation',
            'Infirmier scolaire',
            'Assistant d\'éducation',
            'Chef d\'établissement',
            'Enseignant spécialisé',
            'Médecin scolaire',
            'Orthophoniste',
            'Ergothérapeute',
            'Kinésithérapeute',
            'Responsable pédagogique'
        ];

        for ($i = 1; $i <= 30; $i++) {
            $user = new User();

            $user->setEmail($faker->unique()->safeEmail());
            $user->setRoles(['ROLE_USER']);
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setProfession($faker->randomElement($professions));
            $user->setProfilePicture($faker->imageUrl(300, 300, 'people'));

            $user->setIsCn2eMember(true);
            $user->setCn2eRole($faker->randomElement($rolesCN2E));
            $user->setPhone($faker->phoneNumber());

            $user->setLastLoginAt(\DateTimeImmutable::createFromMutable($faker->dateTime()));
            $user->setIsAccepted(true);
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