<?php
namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PostController extends AbstractController
{
    #[Route('/new-post', name: 'post_new')]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        TokenStorageInterface $tokenStorage
    ): Response {
        // Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
       
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour créer un post');
            return $this->redirectToRoute('app_login');
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
       
        $form->handleRequest($request);
       
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le téléchargement de l'image
            $imageFile = $form->get('images')->getData();
            if ($imageFile) {
                $imageFileName = $fileUploader->upload($imageFile);
                $post->setImages($imageFileName);
            } else {
                $post->setImages('default.jpg'); // Image par défaut si aucune n'est uploadée
            }

            // Définir l'utilisateur du post
            $post->setUser($user);

            // Persister le post
            $entityManager->persist($post);
            $entityManager->flush();
           
            $this->addFlash('success', 'Post créé avec succès !');
           
            return $this->redirectToRoute('app_home');
        }
       
        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

