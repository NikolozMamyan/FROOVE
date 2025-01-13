<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/web', name: 'app_')]
class ChatController extends AbstractController
{




    #[Route('/chat', name: 'chat')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $contacts = $user->getContacts();

        // Créer un tableau avec les derniers messages pour chaque contact
        $contactData = [];
        foreach ($contacts as $contact) {
            $lastMessage = $em->getRepository(Message::class)->createQueryBuilder('m')
            ->where('(m.sender = :user AND m.recipient = :contact) OR (m.sender = :contact AND m.recipient = :user)')
            ->setParameter('user', $user)
            ->setParameter('contact', $contact)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        
    
            $contactData[] = [
                'contact' => $contact,
                'lastMessage' => $lastMessage ? $lastMessage->getContent() : 'Aucun message échangé.',
            ];
        }
    
        return $this->render('chat/show.html.twig', [
            'contactData' => $contactData,
        ]);
    }
    
    #[Route('/chat/{id}', name: 'chat_send')]
    public function sendMessage(User $otherUser, Request $request, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            // L'utilisateur doit être connecté
            return $this->redirectToRoute('login');
        }


        // Récupérer la liste des messages entre $currentUser et $otherUser
        $messages = $em->getRepository(Message::class)->createQueryBuilder('m')
            ->where('(m.sender = :me AND m.recipient = :other) OR (m.sender = :other AND m.recipient = :me)')
            ->setParameter('me', $currentUser)
            ->setParameter('other', $otherUser)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        // Formulaire pour envoyer un message
        $newMessage = new Message();
        $form = $this->createForm(MessageType::class, $newMessage, [
            'csrf_protection' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newMessage->setSender($currentUser);
            $newMessage->setRecipient($otherUser);
            $newMessage->setCreatedAt(new \DateTime());
            $em->persist($newMessage);
            $em->flush();
        
            // Retourne une réponse JSON pour AJAX
            return $this->json(['status' => 'success', 'message' => 'Message envoyé !']);
        }
        

        return $this->render('chat/index.html.twig', [
            'otherUser' => $otherUser,
            'messages' => $messages,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/chat/messages/{id}', name: 'chat_messages')]
public function fetchMessages(User $otherUser, EntityManagerInterface $em): Response
{
    $currentUser = $this->getUser();

    $messages = $em->getRepository(Message::class)->createQueryBuilder('m')
        ->where('(m.sender = :me AND m.recipient = :other) OR (m.sender = :other AND m.recipient = :me)')
        ->setParameter('me', $currentUser)
        ->setParameter('other', $otherUser)
        ->orderBy('m.createdAt', 'ASC')
        ->getQuery()
        ->getResult();

    return $this->render('chat/_messages.html.twig', [
        'messages' => $messages,
        'currentUser' => $currentUser
    ]);
}

}
