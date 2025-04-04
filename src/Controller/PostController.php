<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Like;
use App\Entity\Comment;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/seed-posts', name: 'seed_posts')]
    public function seed(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour créer des posts');
            return $this->redirectToRoute('app_login');
        }

        $post = new Post();
        $post->setDescription('Post test');
        $post->setImages('post1.jpg');
        $post->setUser($user);
        $post->setCreatedAt(new \DateTime());

        $em->persist($post);
        $em->flush();

        $this->addFlash('success', 'Post ajouté avec succès');
        return $this->redirectToRoute('home');
    }

    #[Route('/post/{id}/like', name: 'post_like', methods: ['POST'])]
    public function likePost(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Vous devez être connecté']);
            }
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier si l'utilisateur a déjà liké ce post
        $like = $em->getRepository(Like::class)->findOneBy([
            'post' => $post,
            'user' => $user
        ]);
        
        $isLiked = false;
        
        if ($like) {
            // Si déjà liké, on enlève le like
            $em->remove($like);
            $message = 'Like retiré';
        } else {
            // Sinon on ajoute un like
            $like = new Like();
            $like->setUser($user);
            $like->setPost($post);
            $em->persist($like);
            $message = 'Post liké';
            $isLiked = true;
        }
        
        $em->flush();
        
        // Si la requête est en AJAX
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => $message,
                'likesCount' => count($post->getLikes()),
                'isLiked' => $isLiked
            ]);
        }
        
        // Redirection vers la page précédente
        return $this->redirectToRoute('home');
    }

    #[Route('/post/{id}/comment', name: 'post_comment', methods: ['POST'])]
    public function addComment(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Vous devez être connecté']);
            }
            return $this->redirectToRoute('app_login');
        }
        
        $content = $request->request->get('content');
        
        if (empty($content)) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Le commentaire ne peut pas être vide']);
            }
            $this->addFlash('error', 'Le commentaire ne peut pas être vide');
            return $this->redirectToRoute('home');
        }
        
        $comment = new Comment();
        $comment->setContent($content);
        $comment->setUser($user);
        $comment->setPost($post);
        $comment->setCreatedAt(new \DateTime());
        
        $em->persist($comment);
        $em->flush();
        
        // Si la requête est en AJAX
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'html' => $this->renderView('comment/_comment.html.twig', ['comment' => $comment]),
                'commentCount' => count($post->getComments())
            ]);
        }
        
        $this->addFlash('success', 'Commentaire ajouté');
        return $this->redirectToRoute('home');
    }
}