<?php
// src/Entity/User.php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'Ce nom d\'utilisateur est déjà utilisé')]
class User implements UserInterface, PasswordAuthenticatedUserInterface

{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
   
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $username;
   
    #[ORM\Column(type: 'string', length: 255)]
    private $password;

    private ?string $plainPassword = null;

    // Getters et setters
    public function getId(): ?int
    {
        return $this->id;
    }
   
    public function getUsername(): ?string
    {
        return $this->username;
    }
   
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    // Cette méthode n'est peut-être plus nécessaire selon votre version de Symfony
    public function getSalt(): ?string
    {
        return null; // Pas nécessaire avec bcrypt ou argon2
    }
}



