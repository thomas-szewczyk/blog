<?php


namespace App\Form\Type;


use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['attr' => ['class' => 'col'], 'label' => false])
            ->add('description', TextType::class, ['attr' => ['class' => 'col'], 'label' => false])
            ->add('content', CKEditorType::class, ['attr' => ['class' => 'ckeditor'], 'config' => ['toolbar' => 'standard'], 'label' => false, 'data' => 'Enter the content'])
            ->add('imageFile', FileType::class)
            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn-success']])
        ;
    }
}