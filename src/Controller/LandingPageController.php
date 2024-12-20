<?php

namespace App\Controller;

use App\Repository\AdsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LandingPageController extends AbstractController
{
    #[Route('/', name: 'app_landing_page')]
    public function index(AdsRepository $adRep): Response
    {
        $ads = $adRep->findAll();
        $limit = 3; 
        $ads_limit = array_slice($ads, 0, $limit);


        return $this->render('landing_page/index.html.twig', [
            'ads_limit' => $ads_limit,
        ]);
    }
}
