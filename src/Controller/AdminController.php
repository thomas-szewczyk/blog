<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\RSS\RSSFeed;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Type\PostType;
use App\Entity\Post;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Class AdminController
 * @package App\Controller
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }

    /**
     * @Route("/", name="index")
     * @param PostRepository $postRepository
     * @param UserRepository $userRepository
     * @return Response
     */
    public function index(PostRepository $postRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $username = $user->getUsername();

        if ($this->isGranted('ROLE_ADMIN')) {

            $headlineLikes = 'Most liked post';
            $headlineComments = 'Most commented post';
            $headlineUser = 'User with the most posts';

            $mostLikedPost = $postRepository->findMostLiked();
            $mostCommentedPost = $postRepository->findMostCommented();
            $userWithMostPosts = $userRepository->findUserWithMostPosts();

            return $this->render('admin/dashboard.html.twig', [
                'headlineLikes' => $headlineLikes,
                'headlineComments' => $headlineComments,
                'headlineUser' => $headlineUser,
                'mostLikedPost' => $mostLikedPost,
                'mostCommentedPost' => $mostCommentedPost,
                'userWithMostPosts' => $userWithMostPosts
            ]);

        } else {

            $headlineLikes = 'Your most liked post';
            $headlineComments = 'Your most commented post';

            $mostLikedPost = $postRepository->findMostLikedByUsername($username);
            $mostCommentedPost = $postRepository->findMostCommentedByUsername($username);

            $avgLikes = $postRepository->countAvgLikes($username);
            $avgLikesRounded = round($avgLikes, 2);

            $totalPosts = $postRepository->getNumberOfPostsByUser($username);

            return $this->render('admin/dashboard.html.twig', [
                'headlineLikes' => $headlineLikes,
                'headlineComments' => $headlineComments,
                'mostLikedPost' => $mostLikedPost,
                'mostCommentedPost' => $mostCommentedPost,
                'avgLikes' => $avgLikesRounded,
                'totalPosts' => $totalPosts
            ]);
        }


    }

    /**
     * @Route("/posts", name="list")
     * @param PostRepository $postRepository
     * @return Response
     */
    public function list(PostRepository $postRepository): Response
    {
        $user = $this->getUser();

        if ($hasAccess = $this->isGranted('ROLE_ADMIN')) {
            $headline = 'All Posts';
            $posts = $postRepository->findAllPosts();
        } else {
            $headline = 'Your Posts';
            $posts = $postRepository->findByCreator($user->getUsername());
        }
        return $this->render('admin/list.html.twig', [
            'list' => $posts,
            'headline' => $headline
        ]);
    }

    /**
     * @Route("/create", name="create")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SluggerInterface $slugger
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $post = new Post(); // new post instance

        $form = $this->createForm(PostType::class, $post);  // creates the form to create a new post
        $form->handleRequest($request);

        if ($form->isSubmitted()) {     // check if form was submitted

            $post = $this->handlePostForm($form, $slugger);
            $post->setCreatedAt(new \DateTime());

            $entityManager->persist($post);
            $entityManager->flush();

            $this->updateRSSFeed();

            $this->addFlash('success', 'Post was created!');
            return $this->redirectToRoute('admin_list');
        }

        return $this->render('admin/create.html.twig', [
            'headline' => 'Create post',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route ("/posts/post/{id}/edit", name="edit")
     * @ParamConverter("post", class="App:Post")
     * @param Post $post
     * @param CommentRepository $commentRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SluggerInterface $slugger
     * @return Response
     */
    public function edit(Post $post, CommentRepository $commentRepository, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $post = $this->handlePostForm($form, $slugger);

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post was saved!');
            return $this->redirectToRoute('admin_edit', ['id' => $post->getId()]);
        }

        return $this->render('admin/edit.html.twig', [
            'postComments' => $commentRepository->findByPostId($post->getId()),
            'headline' => 'Edit post',
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/delete/{id}", name="remove")
     * @param Post $post
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse
     */
    public function remove(Post $post, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($post);
        $entityManager->flush();

        $this->updateRSSFeed();

        $this->addFlash('success', 'Post successfully removed!');
        return $this->redirectToRoute('admin_list');
    }

    /**
     * @Route("/user/create", name="new_user")
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function editUser(EntityManagerInterface $entityManager, Request $request, UserPasswordEncoderInterface $passwordEncoder) {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $form->getData();

            $user->setPassword($passwordEncoder->encodePassword($user, $form['password']->getData()));
            $user->addRole($form['choiceRole']->getData());

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User was created!');
            return $this->redirectToRoute('admin_show_users');
        }

        return $this->render('admin/adduser.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/users", name="show_users")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function showUsers(UserRepository $userRepository): Response
    {
        return $this->render('/admin/users.html.twig', [
            'list' => $userRepository->findAllUsers()
        ]);
    }

    private function handlePostForm(FormInterface $form, SluggerInterface $slugger): Post
    {
        $post = $form->getData();
        $imageFile = $form->get('imageFile')->getData();
        $post->setUser($this->getUser());

        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $saveFilename = $slugger->slug($originalFilename);
            $newFilename = $saveFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('image_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
            $post->setImageFile($newFilename);
        } else {
            $post->setImageFile('orionthemes-placeholder-image.png');
        }

        return $post;
    }

    private function updateRSSFeed()
    {
        RSSFeed::generateRssFeed(
            $this->getDoctrine()
                ->getRepository(Post::class)
                ->findAll()
        );

    }

}
