<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class OrderService
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Order", inversedBy="services")
     * @ORM\JoinColumn()
     */
    private $order;

    /**
     * @var Service
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Service")
     * @ORM\JoinColumn()
     */
    private $service;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $cost;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn()
     */
    private $user;

    /**
     * @var OrderPart[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrderPart", mappedBy="orderService")
     */
    private $orderParts;

    public function __construct()
    {
        $this->orderParts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): void
    {
        if ($this->order) {
            throw new \DomainException('Changing order is restricted');
        }

        $this->order = $order;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(Service $service): void
    {
        $this->service = $service;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getCost(): ?int
    {
        return $this->cost;
    }

    public function setCost(int $cost): void
    {
        $this->cost = $cost;
    }

    public function getOrderParts()
    {
        return $this->orderParts;
    }

    public function addOrderPart(OrderPart $orderPart): void
    {
        $this->orderParts[] = $orderPart;
    }

    public function removeOrderPart(OrderPart $orderPart): void
    {
        $this->orderParts->remove($orderPart);
    }

    public function __toString(): string
    {
        return $this->getService()->getName();
    }
}
