<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\CommentLike;
use App\Entity\Post;
use App\Entity\PostLike;
use App\Form\Type\CommentType;
use App\Repository\CommentLikeRepository;
use App\Repository\CommentRepository;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route ("/post/{id}", name="show")
     * @param $id
     * @param PostRepository $postRepository
     * @param CommentRepository $commentRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function show($id, PostRepository $postRepository, CommentRepository $commentRepository, Request $request, EntityManagerInterface $entityManager): Response
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
                'show', ['id' => $id], 302)
            );
        }

        $commentsText = 'comments';
        if($commentsNum = $commentRepository->getTotalNumberByPostId($id)) {
            if($commentsNum = 1) {
                $commentsText = 'comment';
            }
        }

        if($this->getUser()) {
            return $this->render('post/post.html.twig', [
                'post' => $postRepository->find($id),
                'loggedInUser' => $this->getUser()->getUsername(),
                'comments' => $postRepository->find($id)->getComments(),
                'commentsNum' => $commentsNum,
                'commentsText' => $commentsText,
                'form' => $form->createView()
            ]);
        }

        $post = $postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('The post doe not exist');
        }

        return $this->render('post/post.html.twig', [
            'post' => $post,
            'comments' => $post->getComments(),
            'commentsNum' => $commentsNum,
            'commentsText' => $commentsText,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/{id}/like", name="like_post")
     * @param Post $post
     * @param PostLikeRepository $likeRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function likePost(Post $post, PostLikeRepository $likeRepository, EntityManagerInterface $entityManager) : Response
    {
        $user = $this->getUser();
        // Check if the user is authorized to like
        if (!$user) {
            return $this->json(
                [
                    'code' => 403,
                    'message' => 'Access denied!'
                ], 403 );
        }

        // Check if the post is already like by the user => dislike
        if ($post->isLikedByUser($user)) {

            $like = $likeRepository->findOneBy([
                'user' => $user,
                'likedPost' => $post

            ]);

            $entityManager->remove($like);
            $entityManager->flush();

            return $this->json([
                'code' => 200,
                'message' => 'Like removed!',
                'likes' => $likeRepository->count(['likedPost' => $post])
            ], 200);
        }

        $like = new PostLike();
        $like->setUser($user)
            ->setLikedPost($post);

        $entityManager->persist($like);
        $entityManager->flush();

        // Post liked, return status 200
        return $this->json(
            [
                'code' => 200,
                'message' => 'Post liked!',
                'likes' => $likeRepository->count(['likedPost' => $post])
            ], 200);

    }

    /**
     * @Route("/comment/{id}/like", name="like_comment")
     * @param Comment $comment
     * @param CommentLikeRepository $likeRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function likeComment(Comment $comment, CommentLikeRepository $likeRepository, EntityManagerInterface $entityManager) : Response
    {
        $user = $this->getUser();
        // Check if the user is authorized to like
        if (!$user) {
            return $this->json(
                [
                    'code' => 403,
                    'message' => 'Access denied!'
                ], 403 );
        }

        // Check if the post is already like by the user => dislike
        if ($comment->isLikedByUser($user)) {

            $like = $likeRepository->findOneBy([
                'user' => $user,
                'comment' => $comment
            ]);

            $entityManager->remove($like);
            $entityManager->flush();

            return $this->json([
                'code' => 200,
                'message' => 'Comment removed!',
                'likes' => $likeRepository->count(['comment' => $comment])
            ], 200);
        }

        $like = new CommentLike();
        $like->setUser($user)
            ->setComment($comment);

        $entityManager->persist($like);
        $entityManager->flush();

        // Post liked, return status 200
        return $this->json(
            [
                'code' => 200,
                'message' => 'Comment liked!',
                'likes' => $likeRepository->count(['comment' => $comment])
            ], 200);
    }

    /**
     * @Route("comment/{id}/remove", name="remove_comment")
     * @param Comment $comment
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return Response
     */
    public function removeComment(Comment $comment, EntityManagerInterface $entityManager, Request $request): Response
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        if (strpos($request->headers->get('referer'), 'admin') !== false) {
            return $this->redirect($this->generateUrl
            (
                'admin_edit', ['id' => $comment->getPost()->getId()], 302)
            );
        }

        return $this->redirect($this->generateUrl
        (
            'show', ['id' => $comment->getPost()->getId()], 302)
        );
    }
}
