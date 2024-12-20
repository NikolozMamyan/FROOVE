<?php

namespace App\Controller;



use App\Entity\User;

use Twig\Environment;
use Symfony\Component\Uid\Uuid;
use App\Repository\UserRepository;
use App\Service\BrevoEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profil');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }
    #[Route(path: '/signup', name: 'app_register')]
    public function signUp(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager, BrevoEmailService $mailService 
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
    
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $name = $request->request->get('name');
    
            // Validation simple
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password) || empty($name) ) {
                $this->addFlash('error', 'Veuillez remplir tous les champs correctement.');
                return $this->redirectToRoute('app_login');
            }
    
            // Vérification si l'utilisateur existe déjà
            $userRepo = $entityManager->getRepository(User::class);
            if ($userRepo->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'Un compte avec cet email existe déjà.');
                return $this->redirectToRoute('app_login');
            }
    
            // Création de l'utilisateur
            $user = new User();
            $activationToken = Uuid::v4()->toRfc4122();
            $user->setActivationToken($activationToken);
            $user->setEmail($email);
            $user->setFullName($name);

    
    
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            $user->setVerified(false);
            // Sauvegarde dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();
            
            $mailService->mailSender($email, $name, $activationToken);

    
            // Redirection après l'inscription
            $this->addFlash('success', 'Votre compte a été créé avec succès. Veuillez vérifier votre e-mail pour activer votre compte 😊');

            return $this->redirectToRoute('app_login');
        }
    
        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/activate', name: 'app_activate_account')]
    public function activateAccount(Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $token = $request->query->get('token');
    
        // Vérifier si le token existe
        $user = $userRepository->findOneBy(['activationToken' => $token]);
    
        if (!$user) {
            throw $this->createNotFoundException("Token d'activation invalide.");
        }
    
        // Activer le compte de l'utilisateur
        $user->setVerified(true);
        $user->setActivationToken(null);
    
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Votre compte a été activé avec succès.');
    
        // Rediriger vers une page de confirmation
        return $this->redirectToRoute('app_login');
    }
}