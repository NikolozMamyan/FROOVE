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

        // Ajouter un utilisateur admin
        $admin = new User();
        $admin->setEmail('nika.mamian@gmail.com'); // E-mail de l'admin
        $admin->setFullName('Admin Nikoloz');    // Nom complet de l'admin
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'blabla123')); // Mot de passe sécurisé
        $admin->setRoles(['ROLE_ADMIN']);    // Rôle admin
        $admin->setActivationToken(null);    // Pas besoin de token d'activation pour l'admin
        $admin->setCountry('France');
        $admin->setDevise('EUR');
        $admin->setVerified(true);           // Admin déjà vérifié
    
        $manager->persist($admin);
    
        // Sauvegarder les utilisateurs et annonces
        $manager->flush();
    }
    
}
