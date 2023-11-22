<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UtilisateurRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[UniqueEntity(fields: ["username"], message: "Username already exists")]
#[UniqueEntity(fields: ["email"], message: "Email exists already")]
#[UniqueEntity(fields: ["cin"], message: "Cin exists already")]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id=null;

    #[Assert\NotBlank(message:"Name can't be empty")]
    #[ORM\Column(length:30)]
    private ?string $nom= null;

    #[Assert\NotBlank(message:"Lastname can't be empty")]
    #[ORM\Column(length:30)]
    private ?string $prenom=null;

    
   // #[Assert\Date(message:"Invalid date")]
    #[ORM\Column(length:30)]
    private  $dateNaissance=null;

    //unique
 //   #[Assert\Unique(message:"CIN exists already")]
    #[Assert\Length(min:8 , minMessage:"Cin too short")]
    #[ORM\Column(length:10)]
    private ?string $cin=null;

    #[ORM\Column]
    private ?int $age=null;

    #[ORM\Column(length:200)]
    private ?string $pic=null;

 //   #[Assert\Unique(message:"Username already exists")]
    #[ORM\Column(length:20)]
    private ?string $username=null;
    #[Assert\NotBlank(message:"Password can't be null")]
    #[ORM\Column(length:40)]
    private ?string $password=null;
    
    
    
    #[Assert\NotBlank(message:"Email can't be empty")]
    #[Assert\Email(message:"Invalid Email")]
    #[ORM\Column(length:100)]
    private ?string $email=null;

    #[ORM\Column(length:15)]
    private ?string $type="VISITEUR";

   
   

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getDateNaissance(): ?string
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance( $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getCin(): ?string
    {
        return $this->cin;
    }

    public function setCin(string $cin): static
    {
        $this->cin = $cin;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getPic(): ?string
    {
        return $this->pic;
    }

    public function setPic(string $pic): static
    {
        $this->pic = $pic;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }



   


}
