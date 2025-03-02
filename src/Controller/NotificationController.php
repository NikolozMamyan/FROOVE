<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\AdsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/web', name: 'app_')]
class NotificationController extends AbstractController
{
    #[Route('/notifications/{id}/accept', name: 'notification_accept', methods: ['GET'])]
    public function acceptNotification(Notification $notification, EntityManagerInterface $em, AdsRepository $adRepository): Response
    {
        // Récupération des utilisateurs concernés
        $sender = $notification->getSender(); // Utilisateur qui a participé
        $recipient = $notification->getUser(); // Auteur de l'annonce
    
        // Récupération de l'annonce liée
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
    
            $currentUser = $this->getUser();
            if ($currentUser && $currentUser->getId() === $sender->getId()) {
                $this->addFlash('info', "Vous avez utilisé {$price} points pour cette participation.");
            } elseif ($currentUser && $currentUser->getId() === $recipient->getId()) {
                $this->addFlash('success', "Vous avez gagné {$price} points !");
            }
    
            // Mettre à jour la notification actuelle pour indiquer l'acceptation
            $notification->setStatus('accepted'); // Statut accepté
            $notification->setIsRead(true);
            $notification->setMessage('Vous avez accepté la demande de ' . $sender->getFullName());
            
            // Créer une nouvelle notification pour informer l'expéditeur
            $newNotification = new Notification();
            $newNotification->setUser($sender); // Notification pour le participant
            $newNotification->setSender($recipient); // Envoyée par l'auteur de l'annonce
            $newNotification->setMessage($recipient->getFullName() . " a accepté votre demande");
            $newNotification->setAd($ad);
            $newNotification->setStatus('accepted');
            $newNotification->setIsRead(false); // Notification non lue pour l'expéditeur
    
            $em->persist($newNotification);
            $em->flush();
    
        } catch (\Exception $e) {
            $this->addFlash('error', 'Points insuffisants pour accepter la participation.');
            return $this->redirectToRoute('app_ads');
        }
    
        // Mettre à jour l'annonce pour marquer la participation
        $ad->setParticipated(true);
        $ad->setParticipant($sender);
    
        // Ajouter l'expéditeur aux contacts du destinataire si pas déjà fait
        if (!$recipient->getContacts()->contains($sender)) {
            $recipient->addContact($sender);
        }
    
        // Sauvegarder les changements
        $em->persist($notification);
        $em->persist($recipient);
        $em->flush();
    
        return $this->redirectToRoute('app_notification_match', [
            'id' => $notification->getId(),
            '_turbo' => 'false',
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
    #[Route('/notifications/{id}/match', name: 'notification_match', methods: ['GET'])]
    public function matchView(Notification $notification): Response
    {
        // Récupération des utilisateurs concernés
        $sender = $notification->getSender(); // L'utilisateur qui a fait la demande
        $recipient = $notification->getUser(); // L'utilisateur connecté qui a accepté la demande
    
        // Vérifie si les deux utilisateurs sont bien définis
        if (!$sender || !$recipient) {
            throw $this->createNotFoundException('Les utilisateurs concernés sont introuvables.');
        }
    
        // Récupération des informations nécessaires (par ex. les photos de profil)
        // $senderProfilePicture = $sender->getProfilePicture() ?: 'default_sender.jpg';
        // $recipientProfilePicture = $recipient->getProfilePicture() ?: 'default_recipient.jpg';
    
        return $this->render('notification/match.html.twig', [
            'sender' => $sender,
            'recipient' => $recipient,
            // 'senderProfilePicture' => $senderProfilePicture,
            // 'recipientProfilePicture' => $recipientProfilePicture,
        ]);
    }
    

    

}
