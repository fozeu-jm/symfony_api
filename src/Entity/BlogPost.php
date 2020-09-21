<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\BlogPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;

/**
 * @ORM\Entity(repositoryClass=BlogPostRepository::class)
 * @ApiFilter(
 *      SearchFilter::class,
 *         properties={
 *             "id" : "exact",
 *             "title" : "partial",
 *             "content" : "partial",
 *             "author" : "exact",
 *             "author.name" : "partial"
 *         }
 * )
 * @ApiFilter(
 *      DateFilter::class,
 *      properties={
 *          "published"
 *     }
 * )
 * @ApiFilter(
 *    RangeFilter::class,
 *    properties={ "id" }
 * )
 * @ApiFilter(
 *     OrderFilter::class,
 *     properties={
 *          "id","published","title"
 *     },
 *     arguments={"orderParameterName" = "_order"}
 * )
 * @ApiFilter(
 *     PropertyFilter::class,
 *     arguments={
 *          "parameterName" : "properties",
 *          "overrideDefaultProperties":false,
 *          "whitelist" = {"id","title","content","author","slug"}
 *     }
 * )
 * @ApiResource(
 *     attributes={"order" = {"published":"DESC"},
 *          "pagination_client_enabled"=true
 *     },
 *     itemOperations={
 *          "get"={
 *                  "normalization_context"={ "groups"={"single_post"} }
 *           },
 *          "put"={"access_control"="is_granted('ROLE_EDITOR') or (is_granted('ROLE_WRITER') and object.getAuthor() === user)"},
 *          "delete"
 *     },
 *     collectionOperations={
 *          "get",
 *          "post"={"access_control"="is_granted('ROLE_WRITER')"}
 *      },
 *     denormalizationContext={
 *          "groups"={"post"}
 *     }
 * )
 */
class BlogPost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"single_post","post"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=10)
     * @Groups({"single_post","post"})
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Type("datetime")
     * @Groups({"single_post","post"})
     */
    private $published;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(min=20)
     * @Groups({"single_post","post"})
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User",inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"single_post","post"})
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @Groups({"single_post","post"})
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="blogPost")
     * @ApiSubresource()
     * @Groups({"single_post","post"})
     */
    private $comments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Image")
     * @ORM\JoinTable(name="blogpostimagejoin")
     * @Groups({"post","single_post","post"})
     * @ApiSubresource()
     */
    private $images;

    /**
     * BlogPost constructor.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->images = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(\DateTimeInterface $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug($slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return User
     */
    public function getAuthor(): ? User
    {
        return $this->author;
    }

    /**
     * @param User $author
     */
    public function setAuthor(User $author): self
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @return Collection
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image)
    {
        $this->images->add($image);
    }

    public function removeImage(Image $image)
    {
        $this->images->removeElement($image);
    }

    public function __toString(): string
    {
        return $this->title;
    }
}
