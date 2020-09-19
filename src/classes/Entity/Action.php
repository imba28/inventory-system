<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VActions
 *
 * @ORM\Table(name="v_actions", indexes={@ORM\Index(name="PRODUCT", columns={"product_id"}), @ORM\Index(name="CUSTOMER", columns={"customer_id"})})
 * @ORM\Entity
 */
class Action
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
     * @var Product
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /*
     * @var Customer
     * @ORM\ManyToOne(targetEntity="Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="rentDate", type="datetime", nullable=true)
     */
    private $rentdate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="returnDate", type="datetime", nullable=true)
     */
    private $returndate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="expectedReturnDate", type="datetime", nullable=true)
     */
    private $expectedreturndate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createDate", type="datetime", nullable=false)
     */
    private $createdate;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

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

    public function getRentdate(): ?\DateTimeInterface
    {
        return $this->rentdate;
    }

    public function setRentdate(?\DateTimeInterface $rentdate): self
    {
        $this->rentdate = $rentdate;

        return $this;
    }

    public function getReturndate(): ?\DateTimeInterface
    {
        return $this->returndate;
    }

    public function setReturndate(?\DateTimeInterface $returndate): self
    {
        $this->returndate = $returndate;

        return $this;
    }

    public function getExpectedreturndate(): ?\DateTimeInterface
    {
        return $this->expectedreturndate;
    }

    public function setExpectedreturndate(?\DateTimeInterface $expectedreturndate): self
    {
        $this->expectedreturndate = $expectedreturndate;

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

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer): void
    {
        $this->customer = $customer;
    }
}
