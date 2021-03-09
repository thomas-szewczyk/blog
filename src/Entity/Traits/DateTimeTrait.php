<?php


namespace App\Entity\Traits;


use Doctrine\ORM\Mapping as ORM;

trait DateTimeTrait
{
    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}