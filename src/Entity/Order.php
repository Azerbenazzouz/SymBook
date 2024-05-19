<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255 , unique : true)]
    private ?string $reference = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(options : ['default'=>'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $create_at = null;

    /**
     * @var Collection<int, OrderDetails>
     */
    #[ORM\OneToMany(targetEntity: OrderDetails::class, mappedBy: 'orders', orphanRemoval: true, cascade:['persist'])]
    private Collection $orderDetails;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EtatOrder $etat = null;

    #[ORM\Column]
    private ?bool $etatPayement = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PayementType $payement_type = null;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
        $this->create_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTimeImmutable $create_at): static
    {
        $this->create_at = $create_at;

        return $this;
    }

    /**
     * @return Collection<int, OrderDetails>
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetails $orderDetail): static
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->add($orderDetail);
            $orderDetail->setOrders($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): static
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getOrders() === $this) {
                $orderDetail->setOrders(null);
            }
        }

        return $this;
    }

    public function getTotal(): float
    {
        $total = 0.0;
        foreach ($this->orderDetails as $detail) {
            $total += $detail->getPrice() * $detail->getQuantity();
        }
        return $total;
    }

    public function getEtat(): ?EtatOrder
    {
        return $this->etat;
    }

    public function setEtat(?EtatOrder $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function isEtatPayement(): ?bool
    {
        return $this->etatPayement;
    }

    public function setEtatPayement(bool $etatPayement): static
    {
        $this->etatPayement = $etatPayement;

        return $this;
    }

    public function getPayementType(): ?PayementType
    {
        return $this->payement_type;
    }

    public function setPayementType(?PayementType $payement_type): static
    {
        $this->payement_type = $payement_type;

        return $this;
    }

}
