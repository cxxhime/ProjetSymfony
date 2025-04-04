<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(PostRepository $postRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();  // Récupère l'utilisateur connecté

        if (!$user) {
            // Si aucun utilisateur n'est connecté, on peut rediriger ou afficher un message
            return $this->redirectToRoute('app_login');  // Ou un autre comportement
        }

        $post = new Post();
        $post->setDescription('Post test');
        $post->setImages('post1.jpg');
        $post->setUser($user);  // Associe l'utilisateur au post

        $em->persist($post);
        $em->flush();

        $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);
    
        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }
}
