<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\AdsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/web', name: 'app_')]
class NotificationController extends AbstractController
{
    #[Route('/notifications/{id}/accept', name: 'notification_accept', methods: ['GET'])]
    public function acceptNotification(Notification $notification, EntityManagerInterface $em, AdsRepository $adRepository): Response
    {
        $notification->setIsRead(true);
    
        // Récupération des utilisateurs concernés
        $sender = $notification->getSender(); // Utilisateur qui a participé
        $recipient = $notification->getUser(); // Auteur de l'annonce
    
        // Récupération de l'annonce liée (supposons que le message de notification contient un identifiant d'annonce)
        $adTitle = $notification->getMessage(); // Message peut contenir l'annonce
        $ad = $notification->getAd();
    
        if (!$ad) {
            throw $this->createNotFoundException('Annonce introuvable.');
        }

        if ($ad->isParticipated()) {
            $this->addFlash('warning', 'Cette annonce a déjà été acceptée.');
            return $this->redirectToRoute('app_ads');
        }
    
       // Déduire les points de l'utilisateur participant
$price = $ad->getPrice(); // Prix en points de l'annonce
try {
    $sender->removePoints($price);
    $recipient->addPoints($price);

    // Vérifie si l'utilisateur connecté est le sender ou le recipient
    $currentUser = $this->getUser();
    if ($currentUser && $currentUser->getId() === $sender->getId()) {
        $this->addFlash('info', "Vous avez utilisé {$price} points pour cette participation.");
    } elseif ($currentUser && $currentUser->getId() === $recipient->getId()) {
        $this->addFlash('success', "Vous avez gagné {$price} points !");
    }

    $notification = new Notification();
    $notification->setUser($sender); // Auteur de l'annonce
    $notification->setSender($recipient); // Participant
    $notification->setMessage($currentUser->getFullName() . " a accepté votre demande");
    $notification->setAd($ad); // Associer l'annonce
    
    $em->persist($notification);
    $em->flush();

} catch (\Exception $e) {
    $this->addFlash('error', 'Points insuffisants pour accepter la participation.');
    return $this->redirectToRoute('app_ads');
}

        $ad->setParticipated(true);
        $ad->setParticipant($sender);
    
        // Ajouter l'expéditeur aux contacts du destinataire si pas déjà fait
        if (!$recipient->getContacts()->contains($sender)) {
            $recipient->addContact($sender); // Relation bidirectionnelle
            
        }
    
        // Enregistrer dans la base de données
        $em->persist($notification);
        $em->persist($recipient);
        $em->flush();
    
        // Rediriger vers la page de chat
        return $this->redirectToRoute('app_chat_send', [
            'id' => $sender->getId(),
            '_turbo' => 'false', // Désactive Turbo pour cette redirection
        ]);
        
    }
    

    #[Route('/notifications/{id}/reject', name:'notification_reject', methods:['GET'])]
    public function rejectNotification(Notification $notification, EntityManagerInterface $em)
    {
        // Marquer la notification comme lue
        $notification->setIsRead(true);
        $em->flush();

        // Rediriger vers la page souhaitée, par exemple la page actuelle
        return $this->redirectToRoute('app_ads');
    }

    #[Route('/notifications/{id}/read-and-chat', name: 'notification_read_and_chat', methods: ['GET'])]
    public function markNotificationAsReadAndRedirect(
        Notification $notification,
        EntityManagerInterface $em
    ): Response {
        // Marque la notification comme lue
        $notification->setIsRead(true);
        $em->flush();
    
        // Redirige vers le chat avec l'expéditeur de la notification
        return $this->redirectToRoute('app_chat_send', [
            'id' => $notification->getSender()->getId(),
            '_turbo' => 'false', // Désactive Symfony Turbo pour cette redirection
        ]);
        
    }
    

}
