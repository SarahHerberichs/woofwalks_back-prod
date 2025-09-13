<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['location:read']],
    denormalizationContext: ['groups' => ['location:write']],
)]
#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['location:read', 'park:read'])] 
    private ?int $id = null;

    #[ORM\Column(type:'float')]
    #[Assert\NotBlank]
    #[Assert\Range(min: -90, max: 90)]
    #[Groups(['location:write'])]
    private ?float $latitude = null;

    #[ORM\Column(type:'float')]
    #[Assert\NotBlank]
    #[Assert\Range(min: -180, max: 180)]
    #[Groups(['location:write'])]
    private ?float $longitude = null;

    #[ORM\Column(type:'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['location:read', 'location:write','walk:read', 'park:read'])]
    private ?string $name = null;

    #[ORM\Column(type:'string', length:255)]
    #[Assert\NotBlank]
    #[Assert\Length(max:255)]
    #[Groups(['location:write'])]
    private ?string $city = null;

    #[ORM\Column(type:'string', length: 255, nullable: true)] 
    #[Groups(['location:read', 'location:write','walk:read'])]
    private ?string $street = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;
        return $this;
    }
}