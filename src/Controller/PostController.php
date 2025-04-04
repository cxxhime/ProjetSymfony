<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;  // Ajoute cette ligne

class PostController extends AbstractController
{
    #[Route('/seed-posts', name: 'seed_posts')]
    public function seed(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();  // Récupère l'utilisateur connecté

        $post = new Post();
        $post->setDescription('Post test');
        $post->setImages('post1.jpg');
        $post->setUser($user);  // Associe l'utilisateur au post

        $em->persist($post);
        $em->flush();

        $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);
    
        return $this->render('home/index.html.twig', [
            'posts' => $posts,]);

        return new Response('Post inséré en base');
    }
}

