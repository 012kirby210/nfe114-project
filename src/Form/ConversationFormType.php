<?php

namespace App\Form;

use App\Entity\Conversation;
use App\Entity\Invitation;
use App\Entity\Profile;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConversationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre',TextType::class)
            ->add('create_datetime',TextType::class)
            ->add('archived',CheckboxType::class,['required' => false])
            ->add('proprietaire',EntityType::class,
                ['class' => Profile::class])
            ->add('relatedInvitations',EntityType::class,
                ['class' => Invitation::class])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Conversation::class,
        ]);
    }
}
