<?php

namespace App\Form;

use App\Entity\Utilisateur;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThan;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'attr' => ['placeholder' => 'Name'],
                'label'=>'Name',])
            ->add('prenom', null,[
                'attr' => ['placeholder' => 'Lastname'],
                'label'=>'Lastname',] )
            ->add('DateNaissance',BirthdayType::class, [
                'attr' => ['placeholder' => 'BirthDate'],
                'label'=>'Birth Date',
                'mapped' => false,
                ])
            ->add('cin', null, [
                'attr' => ['placeholder' => 'CIN'],
                'label'=>'CIN',])
     /*       ->add('age',null,[
                'attr' => ['placeholder' => 'Age'],
                'label'=>'Age',])*/
            ->add('pic',FileType::class,[
                'mapped'=>false,
                'label'=>'Name',
            ])
            ->add('username', null, [
                'attr' => ['placeholder' => 'Username'],
                'label'=>'Username',])
            ->add('password', PasswordType::class, [
                'attr' => ['placeholder' => 'Password'],
                'label'=>'Password',])
            
            ->add('email', null, [
                'attr' => ['placeholder' => 'Email'],
                'label'=>'Email',])
            ->add('type', ChoiceType::class,[
                'choices'=>['VENDEUR'=> 'VENDEUR',
                'FORMATEUR'=>'FORMATEUR','VISITEUR'=>'VISITEUR'],], [
                'attr' => ['placeholder' => 'Type'],
                'label'=>'Type',])
            ->add('Register',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
