<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\Type\CommentType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/post", name="post")
     */
    public function index(): Response
    {
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }

    /**
     * @Route ("/post/{id}", name="show")
     * @param $id
     * @param PostRepository $postRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function show($id, PostRepository $postRepository, Request $request, EntityManagerInterface $entityManager): Response
    {

        $comment = new Comment();       // new instance for a comment

        $form = $this->createForm(CommentType::class, $comment);    // creates a form for comments
        $user = $this->getUser();   // the logged in user
        $form->handleRequest($request); //
        $post = $postRepository->find($id);

        if ($form->isSubmitted()) {

            $form->getData();
            $comment->setUser($user);
            $comment->setCreatedAt(new \DateTime());
            $comment->setPost($post);

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirect($this->generateUrl
            (
                'show', ['id' => $id], 301)
            );
        }

        return $this->render('post/post.html.twig', [
            'post' => $postRepository->find($id),
            'loggedInUser' => $this->getUser()->getUsername(),
            'comments' => $postRepository->find($id)->getComments(),
            'commentsNum' => count($postRepository->find($id)->getComments()),
            'form' => $form->createView()
        ]);
    }
}
