<?php
// src/Controller/AdminController.php

namespace App\Controller;

use App\Entity\Ads;
use App\Entity\User;
use App\Form\UserType;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $userCount = $entityManager->getRepository(User::class)->count([]);
        $adsCount = $entityManager->getRepository(Ads::class)->count([]);
        $messagesCount = $entityManager->getRepository(Message::class)->count([]);
    
        $latestUsers = $entityManager->getRepository(User::class)
            ->findBy([], ['id' => 'DESC'], 5);
        
        $latestAds = $entityManager->getRepository(Ads::class)
            ->findBy([], ['date' => 'DESC'], 5);
    
        return $this->render('admin/dashboard.html.twig', [
            'userCount' => $userCount,
            'adsCount' => $adsCount,
            'messagesCount' => $messagesCount,
            'latestUsers' => $latestUsers,
            'latestAds' => $latestAds,
        ]);
    }

    #[Route('/users', name: 'users')]
    public function users(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/edit/{id}', name: 'user_edit')]
public function edit(Request $request, User $user, EntityManagerInterface $entityManager, int $id): Response
{
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);
    $user = $entityManager->getRepository(User::class)->find($id);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('admin_users');
    }

    return $this->render('admin/user_form.html.twig', [
        'form' => $form->createView(),
        'user' => $user,
    ]);
}
#[Route('/user/delete/{id}', name: 'user_delete')]
public function delete(User $user, EntityManagerInterface $entityManager): Response
{
    $entityManager->remove($user);
    $entityManager->flush();

    return $this->redirectToRoute('users');
}


    #[Route('/ads', name: 'ads')]
    public function ads(EntityManagerInterface $entityManager): Response
    {
        $ads = $entityManager->getRepository(Ads::class)->findAll();
        
        return $this->render('admin/ads.html.twig', [
            'ads' => $ads,
        ]);
    }

// src/Controller/AdminController.php

#[Route('/messages', name: 'messages')]
public function messages(EntityManagerInterface $entityManager): Response
{
    $qb = $entityManager->createQueryBuilder();
    $conversations = $qb->select('DISTINCT 
            CONCAT(CASE 
                WHEN IDENTITY(m.sender) < IDENTITY(m.recipient) 
                THEN IDENTITY(m.sender) 
                ELSE IDENTITY(m.recipient) 
            END, \'-\', 
            CASE 
                WHEN IDENTITY(m.sender) < IDENTITY(m.recipient) 
                THEN IDENTITY(m.recipient) 
                ELSE IDENTITY(m.sender) 
            END) as conv_id,
            IDENTITY(m.sender) as sender_id,
            IDENTITY(m.recipient) as recipient_id,
            MAX(m.createdAt) as last_message_date,
            COUNT(m.id) as message_count')
        ->from(Message::class, 'm')
        ->groupBy('conv_id, sender_id, recipient_id')
        ->orderBy('last_message_date', 'DESC')
        ->getQuery()
        ->getResult();

    // Récupérer les infos des utilisateurs
    foreach ($conversations as &$conv) {
        $conv['sender'] = $entityManager->getRepository(User::class)->find($conv['sender_id']);
        $conv['recipient'] = $entityManager->getRepository(User::class)->find($conv['recipient_id']);
    }

    return $this->render('admin/messages.html.twig', [
        'conversations' => $conversations
    ]);
}

#[Route('/messages/conversation/{senderId}/{recipientId}', name: 'messages_conversation')]
public function conversation(
    int $senderId, 
    int $recipientId, 
    EntityManagerInterface $entityManager
): Response
{
    $sender = $entityManager->getRepository(User::class)->find($senderId);
    $recipient = $entityManager->getRepository(User::class)->find($recipientId);

    $messages = $entityManager->getRepository(Message::class)
        ->createQueryBuilder('m')
        ->where('m.sender = :sender AND m.recipient = :recipient')
        ->orWhere('m.sender = :recipient AND m.recipient = :sender')
        ->setParameter('sender', $sender)
        ->setParameter('recipient', $recipient)
        ->orderBy('m.createdAt', 'ASC')
        ->getQuery()
        ->getResult();

    return $this->render('admin/conversation.html.twig', [
        'messages' => $messages,
        'sender' => $sender,
        'recipient' => $recipient
    ]);
}
}