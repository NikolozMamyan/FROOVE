<?php


namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Message',
                'required' => true,
            ])
            ->add('_token', HiddenType::class, [
                'mapped' => false,
                'data' => $options['csrf_token_manager']->getToken('send_message')->getValue(),
            ]);
    }
}
