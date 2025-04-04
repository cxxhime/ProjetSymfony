<?php

namespace App\Command;

use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-likes',
    description: 'Ajoute un nombre aléatoire de likes aux posts',
)]
class RandomLikesGenerator extends Command
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Ajout de likes aléatoires aux posts');
        
        // Récupérer tous les posts
        $posts = $this->em->getRepository(Post::class)->findAll();
        
        if (empty($posts)) {
            $io->error('Aucun post trouvé.');
            return Command::FAILURE;
        }
        
        // Récupérer tous les utilisateurs
        $users = $this->em->getRepository(User::class)->findAll();
        
        if (count($users) < 2) {
            $io->error('Pas assez d\'utilisateurs pour ajouter des likes aléatoires.');
            return Command::FAILURE;
        }
        
        // Supprimer les likes existants pour partir d'une base propre
        $likes = $this->em->getRepository(Like::class)->findAll();
        foreach ($likes as $like) {
            $this->em->remove($like);
        }
        $this->em->flush();
        $io->text('Likes existants supprimés.');
        
        // Ajouter des likes aléatoires
        $totalLikes = 0;
        
        foreach ($posts as $post) {
            // Nombre aléatoire de likes entre 3 et 15
            $likesCount = rand(3, 15);
            
            // Mélanger les utilisateurs et prendre un sous-ensemble aléatoire
            shuffle($users);
            $selectedUsers = array_slice($users, 0, min($likesCount, count($users)));
            
            foreach ($selectedUsers as $user) {
                $like = new Like();
                $like->setPost($post);
                $like->setUser($user);
                $this->em->persist($like);
                $totalLikes++;
            }
            
            $io->text('Post #' . $post->getId() . ' : ' . $likesCount . ' likes ajoutés.');
        }
        
        $this->em->flush();
        
        $io->success('Total de ' . $totalLikes . ' likes ajoutés avec succès !');
        
        return Command::SUCCESS;
    }
}