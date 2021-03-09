<?php


namespace App\Security;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedHandler implements \Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException): Response
    {
        return new Response('Access Denied', 403);
    }
}