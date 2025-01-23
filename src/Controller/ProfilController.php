<?php

// src/Controller/ProfileController.php

namespace App\Controller;

use App\Form\ProfileEditType;
use App\Form\CountryDeviseType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConversationRepository;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/web', name: 'app_')]
class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'profil')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
       /** @var User $user */
       $user = $this->getUser();

       // Form pour modifier nom et email
       $formProfileEdit = $this->createForm(ProfileEditType::class, $user);
       $formProfileEdit->handleRequest($request);

       // Form pour modifier pays et devise, affiché directement dans la card
       $formCountryDevise = $this->createForm(CountryDeviseType::class, $user);
       $formCountryDevise->handleRequest($request);

       if ($formProfileEdit->isSubmitted() && $formProfileEdit->isValid()) {
           $em->persist($user);
           $em->flush();
           $this->addFlash('success', 'Profil mis à jour avec succès!');
           return $this->redirectToRoute('app_profil');
       }

       if ($formCountryDevise->isSubmitted() && $formCountryDevise->isValid()) {
           $em->persist($user);
           $em->flush();
           $this->addFlash('success', 'Pays et devise mis à jour avec succès!');
           return $this->redirectToRoute('app_profil');
       }

       $photos = [
           'https://via.placeholder.com/200x200',
           'https://via.placeholder.com/200x200',
           'https://via.placeholder.com/200x200',
       ];

       return $this->render('profil/index.html.twig', [
           'user' => [
               'username' => $user->getFullName(),
               'email' => $user->getEmail(),
               'country' => $user->getCountry(),
               'currency' => $user->getDevise(),
               'avatar' => 'https://via.placeholder.com/150',
               'verified' => $user->isVerified(),
               'premium' => false,
               'about' => "Passionné de rencontres et d’activités originales, j’aime découvrir de nouveaux endroits.",
               'photos' => $photos
           ],
           'formProfileEdit' => $formProfileEdit->createView(),
           'formCountryDevise' => $formCountryDevise->createView(),
       ]);
    }

    #[Route('/notifications', name: 'notif')]
public function notif(NotificationRepository $notifRepo)
{
    $user = $this->getUser();

    $notifications = $notifRepo->findBy(['user' => $user], ['createdAt' => 'DESC']);

    return $this->render('notification/index.html.twig', [
        'notifications' => $notifications,
    ]);
}


    #[Route('/notifications/{id}/mark-read', name: 'notif_mark_read', methods: ['POST'])]
public function markRead($id, NotificationRepository $notifRepo, EntityManagerInterface $em)
{
    $user = $this->getUser();
    if (!$user) {
        // Gérer le cas où l'utilisateur n'est pas connecté
        $this->addFlash('error', 'Vous devez être connecté pour effectuer cette action');
        return $this->redirectToRoute('app_login');
    }

    $notification = $notifRepo->find($id);

    if (!$notification) {
        $this->addFlash('error', 'Notification introuvable.');
        return $this->redirectToRoute('app_notif');
    }

    // Vérifier que cette notification appartient à l'utilisateur connecté
    if ($notification->getUser() !== $user) {
        $this->addFlash('error', 'Cette notification ne vous appartient pas.');
        return $this->redirectToRoute('app_notif');
    }

    // Marquer comme lue
    $notification->setIsRead(true);
    $em->flush();

    $this->addFlash('success', 'Notification marquée comme lue.');
    return $this->redirectToRoute('app_notif');
}

#[Route('/conversation/{id}/accept', name: 'conversation_accept', methods: ['POST'])]
public function acceptConversation($id, EntityManagerInterface $em, ConversationRepository $convRepo): Response
{
    $conversation = $convRepo->find($id);
    if (!$conversation) {
        $this->addFlash('error', 'Conversation introuvable.');
        return $this->redirectToRoute('app_notif');
    }

    $currentUser = $this->getUser();
    if (!$conversation->getParticipants()->contains($currentUser)) {
        $this->addFlash('error', 'Vous ne pouvez pas accepter cette conversation.');
        return $this->redirectToRoute('app_notif');
    }

    $conversation->setAccepted(true);
    $em->flush();

    $this->addFlash('success', 'La conversation a été acceptée.');

    // Rediriger vers la page de la conversation
    return $this->redirectToRoute('app_conversation_show', ['id' => $conversation->getId()]);
}

#[Route('/conversation/{id}/refuse', name: 'conversation_refuse', methods: ['POST'])]
public function refuseConversation($id, EntityManagerInterface $em, ConversationRepository $convRepo, NotificationRepository $notifRepo): Response
{
    $conversation = $convRepo->find($id);
    if (!$conversation) {
        $this->addFlash('error', 'Conversation introuvable.');
        return $this->redirectToRoute('app_notif');
    }

    $currentUser = $this->getUser();
    if (!$conversation->getParticipants()->contains($currentUser)) {
        $this->addFlash('error', 'Vous ne pouvez pas refuser cette conversation.');
        return $this->redirectToRoute('app_notif');
    }

    // Supprimer les notifications liées
    $notifications = $notifRepo->findBy(['conversation' => $conversation]);
    foreach ($notifications as $notification) {
        $em->remove($notification);
    }

    // Supprimer la conversation
    $em->remove($conversation);
    $em->flush();

    $this->addFlash('success', 'La conversation a été refusée.');
    return $this->redirectToRoute('app_notif');
}





}

