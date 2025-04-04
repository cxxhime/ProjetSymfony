<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Like;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-fake-users',
    description: 'Crée des utilisateurs fictifs et des interactions',
)]
class CreateFakeUsersCommand extends Command
{
    private $em;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Création d\'utilisateurs fictifs et de leurs interactions');
        
        // Noms d'utilisateurs fictifs
        $usernames = [
            'sophie_martin',
            'thomas_dubois',
            'emma_petit',
            'lucas_bernard',
            'chloé_durand',
            'nathan_leroy',
            'léa_moreau',
            'hugo_simon',
            'manon_garcia',
            'jules_lambert'
        ];
        
        // Commentaires possibles
        $comments = [
            'Superbe photo !',
            'J\'adore cette publication !',
            'Très belle composition.',
            'Quelle inspiration !',
            'C\'est magnifique !',
            'Wow, impressionnant !',
            'Excellent travail !',
            'J\'aime beaucoup ton style.',
            'Merci pour ce partage.',
            'Continue comme ça !',
            'Génial, j\'adore !',
            'Trop cool cette photo !',
            'Ça me donne plein d\'idées !',
            'Le cadrage est parfait.',
            'Les couleurs sont superbes !'
        ];
        
        // Récupérer tous les posts existants
        $posts = $this->em->getRepository(Post::class)->findAll();
        
        if (empty($posts)) {
            $io->error('Aucun post trouvé. Veuillez d\'abord créer des posts.');
            return Command::FAILURE;
        }
        
        // Créer les utilisateurs fictifs
        $createdUsers = [];
        foreach ($usernames as $username) {
            // Vérifier si l'utilisateur existe déjà
            $existingUser = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
            
            if ($existingUser) {
                $io->text('Utilisateur déjà existant : ' . $username);
                $createdUsers[] = $existingUser;
                continue;
            }
            
            $user = new User();
            $user->setUsername($username);
            
            // Hasher le mot de passe (même pour tous : "password")
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);
            
            // Ajouter une photo de profil aléatoire (utilisez des valeurs par défaut)
            $randomPic = rand(1, 5);
            $user->setProfilePicture('user' . $randomPic . '.jpg');
            
            $this->em->persist($user);
            $createdUsers[] = $user;
            $io->text('Utilisateur créé : ' . $username);
        }
        
        $this->em->flush();
        
        // Ajouter des likes et commentaires aléatoires
        $io->section('Ajout de likes et commentaires...');
        
        foreach ($posts as $post) {
            // Chaque utilisateur a une chance sur deux d'aimer chaque post
            foreach ($createdUsers as $user) {
                if (rand(0, 1) === 1) {
                    $existingLike = $this->em->getRepository(Like::class)->findOneBy([
                        'post' => $post,
                        'user' => $user
                    ]);
                    
                    if (!$existingLike) {
                        $like = new Like();
                        $like->setPost($post);
                        $like->setUser($user);
                        $this->em->persist($like);
                    }
                }
                
                // Chaque utilisateur a une chance sur trois d'ajouter un commentaire
                if (rand(0, 2) === 1) {
                    $comment = new Comment();
                    $comment->setContent($comments[array_rand($comments)]);
                    $comment->setPost($post);
                    $comment->setUser($user);
                    $comment->setCreatedAt(new \DateTime('-' . rand(1, 72) . ' hours'));
                    $this->em->persist($comment);
                }
            }
        }
        
        $this->em->flush();
        
        $io->success('Utilisateurs fictifs, likes et commentaires créés avec succès !');
        
        return Command::SUCCESS;
    }
}