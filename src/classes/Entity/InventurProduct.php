<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VInventurproducts
 *
 * @ORM\Table(name="v_inventurproducts")
 * @ORM\Entity
 */
class InventurProduct
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Inventur")
     * @ORM\JoinColumn(name="inventur_id", referencedColumnName="id", nullable=true)
     */
    private $inventur;

    /**
     * @var string
     *
     * @ORM\Column(name="in_stock", type="string", length=0, nullable=false)
     */
    private $inStock;

    /**
     * @var string
     *
     * @ORM\Column(name="missing", type="string", length=0, nullable=false)
     */
    private $missing = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="deleted", type="string", length=0, nullable=false)
     */
    private $deleted = '0';

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInStock(): ?string
    {
        return $this->inStock;
    }

    public function setInStock(string $inStock): self
    {
        $this->inStock = $inStock;

        return $this;
    }

    public function getMissing(): ?string
    {
        return $this->missing;
    }

    public function setMissing(string $missing): self
    {
        $this->missing = $missing;

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

    /**
     * @return User
     */
    public function getInventur(): User
    {
        return $this->inventur;
    }

    /**
     * @param User $inventur
     */
    public function setInventur(User $inventur): void
    {
        $this->inventur = $inventur;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }


}
