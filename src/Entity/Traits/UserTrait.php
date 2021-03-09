<?php


namespace App\Entity\Traits;


use App\Entity\User;

trait UserTrait
{
    private $user;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $createdBy): self
    {
        $this->user = $createdBy;

        return $this;
    }
}