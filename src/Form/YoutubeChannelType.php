<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\YoutubeChannel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class YoutubeChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', TextType::class, [
                'label' => 'URL de la chaîne YouTube',
                'attr' => [
                    'placeholder' => 'https://www.youtube.com/channel/UCJbPGzawDH1njbqV-D5HqKw',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => YoutubeChannel::class,
        ]);
    }
}
