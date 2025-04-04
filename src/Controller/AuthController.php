<?php
namespace App\Controller;
use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
// Remplacer cet import qui cause l'erreur
// use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
// Par celui-ci (ou utilisez l'authenticator spécifique à votre application)
use Symfony\Component\Security\Http\Authentication\AuthenticatorManager;

class AuthController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
        // Supprimez ces arguments pour l'instant
        // UserAuthenticatorInterface $userAuthenticator,
        // FormLoginAuthenticator $authenticator
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Comparer les mots de passe avant de les hacher
                if ($form->get('plainPassword')->getData() !== $form->get('confirmPassword')->getData()) {
                    $this->addFlash('error', 'Les mots de passe ne correspondent pas');
                    return $this->redirectToRoute('app_register');
                }
                
                // Encoder le mot de passe
                $encodedPassword = $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                );
                $user->setPassword($encodedPassword);
                
                // Sauvegarder l'utilisateur
                $em->persist($user);
                $em->flush();
                
                // Ajouter un message flash de succès
                $this->addFlash('success', 'Votre compte a été créé avec succès !');
                
                // Pour l'instant, revenons à la redirection simple
                return $this->redirectToRoute('app_login', ['successRegister' => 1]);
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Ce nom d\'utilisateur est déjà utilisé. Veuillez en choisir un autre.');
            }
        }
        
        return $this->render('auth/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}







