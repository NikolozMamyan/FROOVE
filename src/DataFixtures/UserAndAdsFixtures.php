<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Ads;
use App\Entity\User;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserAndAdsFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Générer plusieurs utilisateurs
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->email);
            $user->setFullName($faker->name);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $user->setRoles(['ROLE_USER']);
            $activationToken = Uuid::v4()->toRfc4122();
            $user->setActivationToken($activationToken);
            $user->setCountry($faker->country);
            $user->setDevise($faker->randomElement(['EUR', 'USD']));
            $user->setVerified(true);

            $manager->persist($user);

            // Générer entre 1 et 5 annonces pour chaque utilisateur
            for ($j = 0; $j < rand(1, 5); $j++) {
                $ad = new Ads();
                $ad->setTitle($faker->sentence(3));
                $ad->setCategory($faker->randomElement(['Cinéma', 'Dîner', 'Musée', 'Sport']));
                $ad->setLocation($faker->city . ', ' . $faker->country);
                $ad->setDate($faker->dateTimeBetween('+1 days', '+30 days'));
                $ad->setCurrency($user->getDevise());
                $ad->setPrice(rand(50, 500));
                $ad->setUser($user);
                $ad->setDescription($faker->paragraph);

                $manager->persist($ad);
            }
        }

        $manager->flush();
    }
}
