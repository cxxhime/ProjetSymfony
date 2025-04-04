<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostRepository;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy([]);
    
        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }
    
}