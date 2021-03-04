<?php


namespace App\Entity\Traits;


use App\Entity\User;
use Doctrine\Common\Collections\Collection;

trait LikeTrait
{

    protected $likes;

    /**
     * @return Collection
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function isLikedByUser(User $user): bool
    {
        foreach ($this->likes as $like) {

            if($like->getUser() === $user) return true;

        }
        return false;
    }
}