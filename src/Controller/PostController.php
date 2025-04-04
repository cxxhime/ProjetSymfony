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
        $post = new Post();
        $post->setDescription('Post test');
        $post->setImages('post1.jpg');
        $post->setUsername('username_example');  // Exemple de nom d'utilisateur


        $em->persist($post);
        $em->flush();

        return new Response('Post inséré en base');
    }
}

