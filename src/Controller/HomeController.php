<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(PostRepository $postRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);

        // S'il n'y a aucun post, on crée 3 faux posts
        if (count($posts) === 0) {
            $usernames = ['Anonyme1', 'Anonyme2', 'Anonyme3'];

            foreach ($usernames as $i => $username) {
                $randomUser = $em->getRepository(User::class)->findOneBy(['username' => $username]);
                if (!$randomUser) continue;

                $fakePost = new Post();
                $fakePost->setDescription("Post automatique #" . ($i + 1) . " - Bienvenue sur le fil d'actu !");
                $fakePost->setImages("fake" . ($i + 1) . ".jpg");
                $fakePost->setUser($randomUser);
                $fakePost->setCreatedAt(new \DateTimeImmutable());

                $em->persist($fakePost);
            }

            $em->flush();
            $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);
        }

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }
}


