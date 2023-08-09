<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route("/", name: "homepage")]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route("/privacy", name: "privacy")]
    public function privacy(): Response
    {
        return $this->render('privacy.html.twig');
    }

    #[Route("/legal", name: "legal")]
    public function legal(): Response
    {
        return $this->render('legal.html.twig');
    }
}