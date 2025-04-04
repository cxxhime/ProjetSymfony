<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        $posts = [
            [
                'username' => '@user123',
                'pp' => 'images/user.jpg',
                'image' => 'images/post1.png',
                'caption' => 'Coucher de soleil incroyable 🔥'
            ],
            [
                'username' => '@mymqueen',
                'pp' => 'images/user2.jpg',
                'image' => 'images/post2.png',
                'caption' => 'Nouveau contenu dispo en privé 💋'
            ],
            [
                'username' => '@foodlover',
                'pp' => 'images/user1.jpg',
                'image' => 'images/post2.png',
                'caption' => 'Nouveaux plats à tester 😋'
            ]
        ];
    
        return $this->render('home/index.html.twig', [
            'posts' => $posts
        ]);
    }
}
