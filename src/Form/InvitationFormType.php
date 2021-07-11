<?php

namespace App\Form;

use App\Entity\Conversation;
use App\Entity\Invitation;
use App\Entity\Profile;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('host',ProfileFormType::class, [
                'class' => Profile::class
            ])
            ->add('guest',EntityType::class,[
                'class' => Profile::class
                ]
            )
            ->add('conversation',EntityType::class,[
                'class' => Conversation::class
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Invitation::class
        ]);
    }
}
