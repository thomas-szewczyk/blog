<?php


namespace App\Form\Type;


use App\Entity\Category;
use App\Repository\CategoryRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('title', TextType::class, ['attr' => ['class' => 'col'], 'label' => false])
            ->add('description', TextType::class, ['attr' => ['class' => 'col'], 'label' => false])
            ->add('content', CKEditorType::class, ['attr' => ['class' => 'ckeditor'], 'config' => ['toolbar' => 'standard'], 'label' => false ])
            ->add('imageFile', FileType::class, [
                'attr' => ['placeholder' => 'Select an article image'],
                'mapped' => false,
                'required' => false
            ])
            ->add('category', EntityType::class, [
                'class'  => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose a category'
            ])
            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn-success']]);
    }


}