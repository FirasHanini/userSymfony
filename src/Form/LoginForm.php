<?php

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => ['placeholder' => 'Username'],
                'label' => 'Username',
            ])
            ->add('password', PasswordType::class, [
                'attr' => ['placeholder' => 'Password'],
                'label' => 'Password',
            ])
            ->add('Forgot',UrlType::class,[
                'label'=>'Forgot your Password'
            ])
            ->add('Connect',UrlType::class,[
                'label'=>'Connect with your Email'
            ])
            ->add('Login', SubmitType::class)
            
            
        ;
    }
}