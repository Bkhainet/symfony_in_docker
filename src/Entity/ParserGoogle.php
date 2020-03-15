<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParserGoogleRepository")
 */
class ParserGoogle
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $id_Google;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $domaine_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $key_word;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $word;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdGoogle(): ?string
    {
        return $this->id_Google;
    }

    public function setIdGoogle(?string $id_Google): self
    {
        $this->id_Google = $id_Google;

        return $this;
    }

    public function getDomaineName(): ?string
    {
        return $this->domaine_name;
    }

    public function setDomaineName(?string $domaine_name): self
    {
        $this->domaine_name = $domaine_name;

        return $this;
    }

    public function getKeyWord(): ?string
    {
        return $this->key_word;
    }

    public function setKeyWord(?string $key_word): self
    {
        $this->key_word = $key_word;

        return $this;
    }

    public function getWord(): ?string
    {
        return $this->word;
    }

    public function setWord(?string $word): self
    {
        $this->word = $word;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}
