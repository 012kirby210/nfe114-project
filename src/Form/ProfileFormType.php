<?php

namespace App\Form;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username',TextType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            //->add('notificationEmail',CheckboxType::class)
            //->add('notificationDesktop',CheckboxType::class)
            ->add('update',SubmitType::class)
            ->add('user',UserFormType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token'
        ]);
    }
}
