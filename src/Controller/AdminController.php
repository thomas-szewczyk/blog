<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("admin/posts", name="list")
     * @param PostRepository $postRepository
     * @return Response
     */
    public function list(PostRepository $postRepository): Response
    {
        $user = $this->getUser();

        if ($hasAccess = $this->isGranted('ROLE_ADMIN')) {
            $headline = 'All Posts';
        } else {
            $headline = 'Your Posts';
        }

        return $this->render('admin/list.html.twig', [
            'list' => $postRepository->findAll(),
            'postsFromUser' => $user->getPosts(),
            'headline' => $headline

        ]);
    }

    /**
     * @Route("admin/create", name="create")
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
           $post = $form->getData();    // get the form data
           $imageFile = $form->get('imageFile')->getData();

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

           $entityManager->persist($post);
           $entityManager->flush();

           $this->addFlash('success', 'Post was created!');
           return $this->redirectToRoute('list');
        }

        return $this->render('admin/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/delete/{id}", name="remove")
     * @param Post $post
     * @param EntityManagerInterface $entityManager
     */
    public function remove(Post $post, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'Post was removed!');
        return $this->redirectToRoute('list');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }

    /**
     * @Route("/admin/user/create", name="new_user")
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function newUser(EntityManagerInterface $entityManager, Request $request, UserPasswordEncoderInterface $passwordEncoder) {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $form->getData();

            $user->setPassword($passwordEncoder->encodePassword($user, $form['password']->getData()));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User was added!');
            return $this->redirectToRoute('show_users');
        }

        return $this->render('admin/adduser.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/admin/user", name="show_users")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function showUsers(UserRepository $userRepository): Response
    {
        return $this->render('/admin/users.html.twig', [
            'list' => $userRepository->findAll()
        ]);
    }
}
