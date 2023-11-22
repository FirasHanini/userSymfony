<?php

namespace App\Controller;

use App\Entity\Utilisateur;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use App\Service\FileUploader;
use App\Service\UtilisateurService;
use LoginForm;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur', name: 'app_utilisateur')]
    public function index(): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    #[Route('/inscription  ', name: 'add_utilisateur')]
    public function addUtilisateur (ManagerRegistry $man, Request $req, FileUploader $fileUploader, SessionInterface $session,UtilisateurService $service): Response
{
    $user=new Utilisateur();
    $maanger=$man->getManager();
    
    $form=$this->createForm(UtilisateurType::class,$user);
    $form->handleRequest($req);
    if($form->isSubmitted()&& $form->isValid())
    {
        $dateActuelle = new \DateTime();
        $dateNaissance=$form->get('DateNaissance')->getData();
        if ($dateNaissance > $dateActuelle) {
            $form->get('DateNaissance')->addError(new FormError('Birthdate cannot be in the future'));
        }else{
            $imageFile = $form->get('pic')->getData();
        if ($imageFile) 
        {
            $imageFileName = $fileUploader->upload($imageFile);
            $user->setPic($imageFileName);
        }


        $user->setDateNaissance($dateNaissance->format('Y-m-d'));
        $user->setAge($service->calculAge($dateNaissance));

        $code=$service->codeGenerate();

        $service->sendEmail('Verification code', $code,$user->getEmail());


        $session->set('inscrit',$user);
        $session->set('code',$code);
        
     
     
        
        return $this->redirectToRoute('addInBase_utilisateur');
        }
    }
    return $this->renderForm('utilisateur/add.html.twig',[
        'f'=>$form
    ]);
}




#[Route('/verification  ', name: 'addInBase_utilisateur')]
public function savePerson(ManagerRegistry $man, SessionInterface $session,LoggerInterface $logger,Request $req):Response
{

    $maanger=$man->getManager();
   

    $remainingAttempts = $session->get('verification_attempts', 3);
    $codeTrue=$session->get('code');
    $user=$session->get('inscrit');

    $logger->info($user->getNom());
    $logger->info($codeTrue);

    $form = $this->createFormBuilder()
    ->add('code', IntegerType::class,[
        'attr'=> ['placeholder' => 'Verification code'],
        'label'=>'We sent an Email containing a verification code. Please paste it here.',
    ]) 
    ->add('Send', SubmitType::class)
    ->getForm();
    $form->handleRequest($req);
    if($form->isSubmitted())
    {
        $code=$form->get('code')->getData();
        if($codeTrue!=$code)
        {
            $remainingAttempts--;
            $session->set('verification_attempts', $remainingAttempts);
            $form->get('code')->addError(new FormError('Wrong verification code! Try again.'));

            if( $remainingAttempts===0)
            {
                return $this->redirectToRoute('login_utilisateur');
            }
        }
        else{
            
            $maanger->persist($user);
            $maanger->flush();
            $session->set('user',$user);

            return $this->redirectToRoute('one_utilisateur',['id'=>$user->getId()]);
        }


    }



return $this->renderForm('utilisateur/verif.html.twig', [
    'f' => $form
]);
}























#[Route('/updateutilisateur/{id}',name:'utilisateur_update')]
public function updateAuthor(Request $requ,ManagerRegistry $manager,$id,UtilisateurRepository $repo, FileUploader $fileUploader,UtilisateurService $service): Response
{
    $mR= $manager->getManager();
    $user= $repo->find($id);
    $form=$this->createForm(UtilisateurType::class,$user);
    $form->handleRequest($requ);
    if($form->isSubmitted()&& $form->isValid())
    {
        $imageFile = $form->get('pic')->getData();
        if ($imageFile) 
        {
            $imageFileName = $fileUploader->upload($imageFile);
            $user->setPic($imageFileName);
        }
        
        $dateActuelle = new \DateTime();
        $dateNaissance=$form->get('DateNaissance')->getData();
        if ($dateNaissance > $dateActuelle) {
            $form->get('DateNaissance')->addError(new FormError('Birthdate cannot be in the future'));
        }else{


            $user->setDateNaissance($dateNaissance->format('Y-m-d'));
        $user->setAge($service->calculAge($dateNaissance));

    $mR->persist($user);
    $mR->flush();
    
    return $this->redirectToRoute('one_utilisateur',['id'=>$user->getId()]);
    }
    }
    return $this->renderForm('utilisateur/add.html.twig',[
    'f'=>$form
]);
}







    #[Route('/utilisateurs', name: 'all_utilisateur')]
    public function getAll(UtilisateurRepository $repo, SessionInterface $session):Response
    {
        $user=$session->get('user');
        $list=$repo->findAll();
        return $this->render('utilisateur/getall.html.twig',[
            'list'=>$list,
            'u'=>$user
        ]);

    }

    #[Route('/utilisateurs/{id}', name: 'one_utilisateur')]
    public function getOne(UtilisateurRepository $repo, $id):Response
    {
        $user=$repo->find($id);
        $user->setPic( "/uploads/" . $user->getPic());

        return $this->render('utilisateur/one.html.twig',[
            'user'=>$user
        ]);
    }

    #[Route('/profil', name: 'profil_utilisateur')]
    public function Profil(UtilisateurRepository $repo,SessionInterface $session ):Response
    {
        $user=$session->get('user');
        $user->setPic( "/uploads/" . $user->getPic());

        return $this->render('utilisateur/one.html.twig',[
            'user'=>$user
        ]);
    }





    #[Route('/utilisateursdelete/{id}', name: 'delete_utilisateur')]
    public function delete(UtilisateurRepository $repo,ManagerRegistry $manager,$id):Response
    {
        $user=$repo->find($id);
        $mr=$manager->getManager();
        $mr->remove($user);
        $mr->flush();

        return $this->redirectToRoute('login_utilisateur');

    }

    #[Route('/login', name: 'login_utilisateur')]
    public function login(UtilisateurRepository $repo, Request $req, SessionInterface $session): Response
    {
        $user= new Utilisateur();
        $form=$this->createForm(LoginForm::class);
        $form->handleRequest($req);
        $lab=null;
    if($form->isSubmitted())
    {
        
        $username = $form->get('username')->getData();
        $password = $form->get('password')->getData();

        $user = $repo->getPaswwdByUsername($username);
    if($password== $user->getPassword())
    {
        $session->set('user',$user);
        return $this->redirectToRoute('all_utilisateur');
    }
    
        $lab="Bad Credentials";
    }
    
    return $this->renderForm('utilisateur/login.html.twig',[
        'f'=>$form,
        'l'=>$lab
    ]);

    }


    #[Route('/logout', name: 'logout_utilisateur')]
    public function logout(SessionInterface $session):Response
    {
        $session->remove('user');

        return $this->redirectToRoute('login_utilisateur');
    }


    public function isAdmin(SessionInterface $session): bool
    {
        if($session->has('user')){
            $user=new Utilisateur();
            $user=$session->get('user');
            if($user->getType=="ADMIN"){
                return true;
            }
            return false;
        }

    }


    #[Route('/getUser', name: 'get_current_utilisateur')]
    public function getcurrent(SessionInterface $session):string
    {
        $user = new Utilisateur();
        $user=$session->get('user');

        $retour= $user->getNom().$user->getPrenom();
        return $retour;

    }




    #[Route('/testmail', name: 'mailing')]
    public function sendEmail(UtilisateurService $se):Response
    {
        $se->sendEmail('Verification code', 'ml','firas.hanini@esprit.tn');

        return $this->render('utilisateur/mail.html.twig',[
         //   'l'=>$retour
        ]);
    }

}
