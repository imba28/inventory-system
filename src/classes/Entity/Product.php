<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * VProducts
 *
 * @ORM\Table(name="v_products", uniqueConstraints={@ORM\UniqueConstraint(name="invNr", columns={"invNr"})})
 * @ORM\Entity
 */
class Product
{
    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=500, nullable=false)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="invNr", type="string", length=45, nullable=true)
     */
    private $invnr;

    /**
     * @var string|null
     *
     * @ORM\Column(name="type", type="string", length=50, nullable=true)
     */
    private $type;

    /**
     * @var string|null
     *
     * @ORM\Column(name="note", type="text", length=65535, nullable=true)
     */
    private $note;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="condition", type="string", length=50, nullable=true)
     */
    private $condition;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @var ProductImage[]
     * @ORM\OneToMany(targetEntity="ProductImage", mappedBy="product")
     */
    private $images;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createDate", type="datetime", nullable=false)
     */
    private $createdate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="stamp", type="datetime", nullable=true)
     */
    private $stamp;

    /**
     * @var string
     *
     * @ORM\Column(name="deleted", type="string", length=0, nullable=false)
     */
    private $deleted = '0';

    public function getId(): ?int
    {
        return $this->id;
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

    public function getInvnr(): ?string
    {
        return $this->invnr;
    }

    public function setInvnr(?string $invnr): self
    {
        $this->invnr = $invnr;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(?string $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function getCreatedate(): ?\DateTimeInterface
    {
        return $this->createdate;
    }

    public function setCreatedate(\DateTimeInterface $createdate): self
    {
        $this->createdate = $createdate;

        return $this;
    }

    public function getStamp(): ?\DateTimeInterface
    {
        return $this->stamp;
    }

    public function setStamp(?\DateTimeInterface $stamp): self
    {
        $this->stamp = $stamp;

        return $this;
    }

    public function getDeleted(): ?string
    {
        return $this->deleted;
    }

    public function setDeleted(string $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFrontImage()
    {
        foreach ($this->images as $image) {
            if ($image->getTitle() == 'frontimage') {
                return $image;
            }
        }

        if (count($this->images) > 0) {
            return $this->images[0];
        }

        return new ProductImage(
            array(
                'src' => 'http://via.placeholder.com/200x200',
                'title' => 'frontimage'
            )
        );
    }

    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * @return ProductImage[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param ProductImage[] $images
     */
    public function setImages(array $images): void
    {
        $this->images = $images;
    }
}
