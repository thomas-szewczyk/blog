<?php


namespace App\Service;


use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessRestrictionsChecker
{
    /**
     * @param string $username
     * @param string $postAuthor
     */
    public function checkAccessRestrictions(string $username, string $postAuthor)
    {
        if($username !== $postAuthor) {
            throw new AccessDeniedException();
        }
    }
}