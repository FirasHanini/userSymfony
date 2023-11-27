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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
    public function addUtilisateur (ManagerRegistry $man, Request $req, FileUploader $fileUploader, 
                                    SessionInterface $session,UtilisateurService $service,
                                    ): Response
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

            $pass=$form->get('password')->getData();
            if($service->isWeakPassword($pass))
            {
                $form->get('password')->addError(new FormError('Your password is weak!')); 
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
    }
    $l=true;
    return $this->renderForm('utilisateur/add.html.twig',[
        'f'=>$form,
        'l'=>$l
    ]);
}




#[Route('/verification  ', name: 'addInBase_utilisateur')]
public function savePerson(ManagerRegistry $man, SessionInterface $session,LoggerInterface $logger,Request $req):Response
{

    $maanger=$man->getManager();
   

    $remainingAttempts = $session->get('verification_attempts', 3);
    $codeTrue=$session->get('code');
    $user=$session->get('inscrit');

   

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
                $session->remove('code');
                $session->remove('inscrit');
                return $this->redirectToRoute('login_utilisateur');
            }
        }
        else{
            
            $maanger->persist($user);
            $maanger->flush();
            $session->set('user',$user);
            $session->remove('code');
            $session->remove('inscrit');

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
    $l=false;
    return $this->renderForm('utilisateur/add.html.twig',[
    'f'=>$form,
    'l'=>$l
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
        $sUser=$session->get('user');
        if(!$sUser)
        {
            return $this->redirectToRoute('login_utilisateur');
        }
        $user= $repo->find($sUser->getId());
       
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
     //   $user= new Utilisateur();
      $sUser=$session->get('user');
      
        if($sUser)
        {
            return $this->redirectToRoute('profil_utilisateur');
        }else
        {

        $form=$this->createForm(LoginForm::class);
        $form->handleRequest($req);
        $lab=null;
    if($form->isSubmitted())
    {
        
        $username = $form->get('username')->getData();
        $password = $form->get('password')->getData();

       $user=$repo->findOneBy(['username'=>$username]);

      if(!$user)
      {

        $form->get('username')->addError(new FormError('User Not Found'));
          
      }
        
    else if($user->getPassword()==$password)
    {
        $session->set('user',$user);
        return $this->redirectToRoute('all_utilisateur');
    }else
    {
        $form->get('password')->addError(new FormError('Wrong Password'));
        
    }
    
     
    }
    
    return $this->renderForm('utilisateur/login.html.twig',[
        'f'=>$form,
        'l'=>$lab
    ]);

}
    }



    #[Route('/Accountrecover', name: 'passwordForgot_utilisateur')]
    public function forgotPassword(Request $req,Request $re, UtilisateurRepository $repo, UtilisateurService $service, SessionInterface $session):Response
    {
        $session->remove('aa');

        $form = $this->createFormBuilder()
    ->add('username', TextType::class,[
        'attr'=> ['placeholder' => 'Username'],
        'label'=>'Please enter your username.',
    ]) 
    ->add('Send', SubmitType::class)
    ->getForm();

    $form2= $this->createFormBuilder()
    ->add('code', IntegerType::class,[
        'attr'=> ['placeholder' => 'Verification code'],
        'label'=>'We sent an Email containing a verification code. Please paste it here.',
    ]) 
    ->add('Confirm', SubmitType::class)
    ->getForm();
    $isForm1Submitted=false;
    $code="";

    $form->handleRequest($req);
    if($form->isSubmitted())
    {
        $isForm1Submitted = true;
        $username=$form->get('username')->getData();
        $user=$repo->findOneBy(['username'=>$username]);
        if(!$user)
        {
            $form->get('username')->addError(new FormError('User not found!'));
        }
        $code= $service->codeGenerate();
        $service->sendEmail('Password forgot',"Hello".$user->getUsername()."your verification code is: \n".$code,
                                $user->getEmail());

    }

    $remainingAttempts = $session->get('aa', 4);
    $form2->handleRequest($re);
    if($form2->isSubmitted()&& !$isForm1Submitted)
    {
        
        $entred=$form2->get('code')->getData();
        if($code==$entred)
        {

            $session->remove('aa');
            return $this->redirectToRoute('one_utilisateur',['id'=>$user->getId()]);

        }else
        {
            $remainingAttempts--;
            $session->set('aa', $remainingAttempts);
            if($remainingAttempts<3)
            {
            $form2->get('code')->addError(new FormError('Wrong verification code! Try again.'));}

            if( $remainingAttempts===0)
            {
                $session->remove('aa');
              
                return $this->redirectToRoute('login_utilisateur');
            }
            

        }
    }
    
    return $this->renderForm('utilisateur/forgot.html.twig',[

        's'=> $isForm1Submitted,
        'f'=>$form,
        'ff'=>$form2
    ]);
}




    #[Route('/mdpreset/{id}', name: 'passwordreset_utilisateur')]
    public function resetPasword($id,UtilisateurRepository $repo, ManagerRegistry $manager, Request $req):Response
    {
        $form= $this->createFormBuilder()
    ->add('PasswordOne', PasswordType::class,[
        'attr'=> ['placeholder' => 'Password'],
        'label'=>'Enter your new password.',
    ]) 
    ->add('PasswordTwo', PasswordType::class,[
        'attr'=> ['placeholder' => 'Password'],
        'label'=>'Confirm your new password.',
    ]) 
    ->add('Confirm', SubmitType::class)
    ->getForm();



        $m=$manager->getManager();
        $user=$repo->find($id);

        $form->handleRequest($req);

        if($form->isSubmitted())
        {
            $one=$form->get('PasswordOne')->getData();
            $two=$form->get('PasswordTwo')->getData();

            if($one!=$two)
            {
                $form->get('PasswordTwo')->addError(new FormError('Passwords mismatch !'));
            }else
            {
                $user->setPassword($two);
                $m->persist($user);
                $m->flush();
            }

        
        }




        return $this->renderForm('utilisateur/reset.html.twig',[
            'f'=>$form
        ]);
    }
























    #[Route('/emailconnect', name: 'emailConnection_utilisateur')]
    public function emailConnection(Request $req,Request $re, UtilisateurRepository $repo, UtilisateurService $service, SessionInterface $session):Response
    {
        $session->remove('aa');
        $form = $this->createFormBuilder()
    ->add('username', TextType::class,[
        'attr'=> ['placeholder' => 'Username'],
        'label'=>'Please enter your username.',
    ]) 
    ->add('Send', SubmitType::class)
    ->getForm();

    $form2= $this->createFormBuilder()
    ->add('code', IntegerType::class,[
        'attr'=> ['placeholder' => 'Verification code'],
        'label'=>'We sent an Email containing a verification code. Please paste it here.',
    ]) 
    ->add('Confirm', SubmitType::class)
    ->getForm();
    $isForm1Submitted=false;
    
    $code="";

    $form->handleRequest($req);
    if($form->isSubmitted())
    {
        $isForm1Submitted = true;
        
        $username=$form->get('username')->getData();
        $user=$repo->findOneBy(['username'=>$username]);
        if(!$user)
        {
            $form->get('username')->addError(new FormError('User not found!'));
        }
        $code= $service->codeGenerate();
        $service->sendEmail('Connection code',"Hello".$user->getUsername()."your verification code is: \n".$code,
                                $user->getEmail());

    }




    
    $remainingAttempts = $session->get('aa', 4);
   
    $form2->handleRequest($re);
    if($form2->isSubmitted())
    {
        
        $entred=$form2->get('code')->getData();
        if($code==$entred)
        {
            $session->remove('aa');
            return $this->redirectToRoute('one_utilisateur',['id'=>$user->getId()]);

        }else
        {
            $remainingAttempts--;
            $session->set('aa', $remainingAttempts);
            
            if($remainingAttempts<3)
            {
            $form2->get('code')->addError(new FormError('Wrong verification code! Try again.'));}

            if( $remainingAttempts===0)
            {
              
                $session->remove('aa');
                return $this->redirectToRoute('login_utilisateur');
            }
            

        }
    }


        return $this->renderForm('utilisateur/forgot.html.twig',[

            's'=> $isForm1Submitted,
            'f'=>$form,
            'ff'=>$form2
        ]);
    }
















    



    #[Route('/logout', name: 'logout_utilisateur')]
    public function logout(SessionInterface $session):Response
    {
        $session->remove('user');

        return $this->redirectToRoute('login_utilisateur');
    }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    #[Route('/testmail', name: 'mailing')]
    public function sendEmail(UtilisateurService $se):Response
    {
        

        return $this->render('utilisateur/mail.html.twig',[
         //   'l'=>$retour
        ]);
    }



    #[Route('/take-photo', name: 'take_photo')]
    public function takePic():Response
    {

      return  $this->render('utilisateur/takePicture.html.twig');
    }







    #[Route('/capture-photo', name: 'capture_photo')]
public function capturePhoto(SessionInterface $session, UtilisateurRepository $repo, ManagerRegistry $manager):Response 
{
    $folderPath = 'capture_images/';
$image_parts = explode(";base64,", $_POST['image']);
$image_type_aux = explode("image/", $image_parts[0]);
$image_type = $image_type_aux[1];
$image_base64 = base64_decode($image_parts[1]);
$file = $folderPath . uniqid() . '.png';
$sUser=$session->get('user');
$user=$repo->find($sUser->getId());
 $fileName = $user->getUsername() . uniqid() . '.png';
  
file_put_contents('uploads/' . $fileName, $image_base64);

//////////////////////////////////UPDATE PIC//////////////////////////////////////

        $m=$manager->getManager();
        
        $user->setPic($fileName);

    
        
        $m->persist($user);
        $m->flush();

        


    return $this->redirectToRoute('profil_utilisateur');
}


















}
