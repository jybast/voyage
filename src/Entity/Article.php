<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
/* Extensions */
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 * 
 * @ORM\Table(name="Article", indexes={@ORM\Index(columns={"titre", "soustitre", "contenu"}, flags={"fulltext"})})
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $soustitre;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Slug(fields={"titre"})
     */
    private $slug;

    /**
     * @ORM\Column(type="date")
     * @Gedmo\Timestampable(on="create")
     */
    private $publierAt;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Gedmo\Timestampable(on="change", field={"titre", "contenu"})
     */
    private $modifierAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $valide = false;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $auteur;

    /**
     * @ORM\OneToMany(targetEntity=Commentaire::class, mappedBy="article", orphanRemoval=true)
     */
    private $commentaires;

    /**
     * @ORM\OneToMany(targetEntity=Media::class, mappedBy="article", cascade={"persist"})
     */
    private $media;

    /**
     * @ORM\ManyToMany(targetEntity=Motcle::class, mappedBy="article")
     */
    private $motcles;

    /**
     * @ORM\ManyToMany(targetEntity=Categorie::class, mappedBy="article")
     */
    private $categories;

    /**
     * @ORM\Column(type="text")
     */
    private $contenu;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="favoris")
     */
    private $favoris;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->motcles = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->favoris = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getSoustitre(): ?string
    {
        return $this->soustitre;
    }

    public function setSoustitre(?string $soustitre): self
    {
        $this->soustitre = $soustitre;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPublierAt(): ?\DateTimeInterface
    {
        return $this->publierAt;
    }

    public function setPublierAt(\DateTimeInterface $publierAt): self
    {
        $this->publierAt = $publierAt;

        return $this;
    }

    public function getModifierAt(): ?\DateTimeInterface
    {
        return $this->modifierAt;
    }

    public function setModifierAt(?\DateTimeInterface $modifierAt): self
    {
        $this->modifierAt = $modifierAt;

        return $this;
    }

    public function getValide(): ?bool
    {
        return $this->valide;
    }

    public function setValide(bool $valide): self
    {
        $this->valide = $valide;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * @return Collection|Commentaire[]
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setArticle($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->contains($commentaire)) {
            $this->commentaires->removeElement($commentaire);
            // set the owning side to null (unless already changed)
            if ($commentaire->getArticle() === $this) {
                $commentaire->setArticle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(Media $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media[] = $medium;
            $medium->setArticle($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): self
    {
        if ($this->media->contains($medium)) {
            $this->media->removeElement($medium);
            // set the owning side to null (unless already changed)
            if ($medium->getArticle() === $this) {
                $medium->setArticle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Motcle[]
     */
    public function getMotcles(): Collection
    {
        return $this->motcles;
    }

    public function addMotcle(Motcle $motcle): self
    {
        if (!$this->motcles->contains($motcle)) {
            $this->motcles[] = $motcle;
            $motcle->addArticle($this);
        }

        return $this;
    }

    public function removeMotcle(Motcle $motcle): self
    {
        if ($this->motcles->contains($motcle)) {
            $this->motcles->removeElement($motcle);
            $motcle->removeArticle($this);
        }

        return $this;
    }

    /**
     * @return Collection|Categorie[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategorie(Categorie $categorie): self
    {
        if (!$this->categories->contains($categorie)) {
            $this->categories[] = $categorie;
            $categorie->addArticle($this);
        }

        return $this;
    }

    public function removeCategorie(Categorie $categorie): self
    {
        if ($this->categories->contains($categorie)) {
            $this->categories->removeElement($categorie);
            $categorie->removeArticle($this);
        }

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(User $favori): self
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris[] = $favori;
        }

        return $this;
    }

    public function removeFavori(User $favori): self
    {
        if ($this->favoris->contains($favori)) {
            $this->favoris->removeElement($favori);
        }

        return $this;
    }
}
