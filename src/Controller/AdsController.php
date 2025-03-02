<?php

namespace App\Controller;

use App\Entity\Ads;
use App\Entity\Notification;
use App\Repository\AdsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/web', name: 'app_')]
class AdsController extends AbstractController
{
    #[Route('/ads', name: 'ads', methods: ['GET'])]
    public function index(Request $request, AdsRepository $adsRepository, NotificationRepository $notificationRepository): Response
    {
        // Récupération des filtres depuis la requête GET
        $category = $request->query->get('category');
        $location = $request->query->get('location');
        $currency = $request->query->get('currency');

        // On va chercher les annonces filtrées
        $ads = $adsRepository->findFilteredAds($category, $location, $currency);

        // $unreadNotifications = $notificationRepository->findBy([
        //     'user' => $this->getUser(),
        //     'isRead' => false
        // ]);
        $persons = [
            ['name' => 'Katty pary', 'role' => 'Student', 'image' => '/path/to/image1.jpg'],
            ['name' => 'Shakira', 'role' => 'Student', 'image' => '/path/to/image2.jpg'],
            ['name' => 'Katty pary', 'role' => 'Student', 'image' => '/path/to/image3.jpg'],
            ['name' => 'Shakira', 'role' => 'Student', 'image' => '/path/to/image4.jpg'],
            ['name' => 'Katty pary', 'role' => 'Student', 'image' => '/path/to/image5.jpg'],
            ['name' => 'Shakira', 'role' => 'Student', 'image' => '/path/to/image6.jpg'],
        ];
        
        return $this->render('ads/index.html.twig', [
            'ads' => $ads,
            'persons' =>$persons,
            'selectedCategory' => $category,
            'selectedLocation' => $location,
            'selectedCurrency' => $currency,
            // 'unreadCount' => count($unreadNotifications),
            // 'unreadNotifications' => $unreadNotifications
        ]);
    }


    #[Route('/ads/{id}/participate', name: 'ads_participate', methods: ['POST'])]
    public function participate(int $id, AdsRepository $adRepository, EntityManagerInterface $em): JsonResponse
    {
        // Récupérer l'annonce
        $ad = $adRepository->find($id);
        if (!$ad) {
            return new JsonResponse(['success' => false, 'error' => 'Annonce introuvable.'], 404);
        }

        // Récupérer l'utilisateur connecté (celui qui participe)
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté.'], 403);
        }
        $adPrice = $ad->getPrice(); // Supposez que l'annonce a un prix en points
        if ($currentUser->getPoints() < $adPrice) {
            return new JsonResponse(['success' => false, 'error' => 'Vous n\'avez pas assez de points pour participer.'], 400);
        }

        // Création de la notification pour l'auteur de l'annonce
        $notification = new Notification();
        $notification->setUser($ad->getUser()); // Auteur de l'annonce
        $notification->setStatus('pending');
        $notification->setSender($currentUser); // Participant
        $notification->setMessage($currentUser->getFullName() . " souhaite participer à votre annonce \"" . $ad->getTitle() . "\"");
        $notification->setAd($ad); // Associer l'annonce
        
        $em->persist($notification);
        $em->flush();

        // Ici vous pouvez également gérer la participation en elle-même (ajouter l'user à la liste des participants de l'ad, etc.)
        // Et éventuellement rediriger l'utilisateur vers une autre page.

        return new JsonResponse([
            'success' => true,
            'redirectUrl' => $this->redirectToRoute('app_ads') // Par exemple, retour à la liste des annonces
        ]);
    }

    public function nav( NotificationRepository $notificationRepository, $current_route = null): Response
    {
      

        $unreadNotifications = $notificationRepository->findBy([
            'user' => $this->getUser(),
            'isRead' => false
        ]);
        $user  = $this->getUser();
        $userPoints = $user ? $user->getPoints() : 0;
        
        return $this->render('components/navBar.html.twig', [
            'unreadCount' => count($unreadNotifications),
            'unreadNotifications' => $unreadNotifications,
            'userPoints' => $userPoints,
            'current_route' => $current_route,
        ]);
    }
    #[Route('/ads/create', name: 'ads_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ad = new Ads();
        $ad->setTitle($request->request->get('title'));
        $ad->setCategory($request->request->get('category'));
        $ad->setLocation($request->request->get('location'));
        $ad->setDate(new \DateTime($request->request->get('date')));
        $ad->setPrice((int) $request->request->get('price'));
        $ad->setCurrency($request->request->get('currency'));
        $ad->setDescription($request->request->get('description'));
        $ad->setUser($this->getUser());

        $entityManager->persist($ad);
        $entityManager->flush();

        $this->addFlash('success', 'Annonce créée avec succès.');
        return $this->redirectToRoute('app_ads');
    }
}
