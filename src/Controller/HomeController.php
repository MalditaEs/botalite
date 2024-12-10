<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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

    #[Route("/onboarding", name: "contact", methods: ["POST"])]
    public function onboarding(Request $request, HttpClientInterface $httpClient): JsonResponse
    {

        $data = json_decode($request->getContent(), true);
        $waba = $data['processData']['waba']['data']['waba_id'];

        $url = 'https://graph.facebook.com/v21.0/oauth/access_token';

        $queryParams = [
            'client_id' => '621829865763918',
            'client_secret' => 'a28c410c7ad48911eaf2cb995a60c658',
            'code' => $data['processData']['code'],
        ];

        try {
            $response = $httpClient->request('GET', $url, [
                'query' => $queryParams
            ]);

            $content = $response->getContent();

            $token = json_decode($content)->access_token;
            $url = "https://graph.facebook.com/v21.0/{$waba}/subscribed_apps";
            $headers = [
                'Authorization' => 'Bearer ' . $token,
            ];

            $response = $httpClient->request('POST', $url, [
                'headers' => $headers,
            ]);

            $content = $response->getContent();

            return new JsonResponse(json_decode($content, true));
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

    }
}