<?php

namespace App\Service;

use DateTime;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class UtilisateurService{

    private $mailer;
    private $session;

    public function __construct(MailerInterface $mailer,SessionInterface $session)
    {
        $this->mailer = $mailer;
        $this->session=$session;
    }


    public function calculAge(\DateTime $dateNaissance):int
    {

        $moisAnniv=$dateNaissance->format('n');
        $currentDate=new \DateTime();

        $moisCur=$currentDate->format('n');

        $difference=$currentDate->diff($dateNaissance);
        if($moisCur>$moisAnniv){
        return $difference->y ;
        }else{
            return ($difference->y)-1;
        }
    }


    public function sendEmail(string $subject,string $text,string $email)
    {
       
        $email = (new Email())
            ->from('ecoartteampi@gmail.com')
            ->to($email)
            ->subject($subject)
            ->text($text);
       //     ->html('<p>Contenu du message en HTML</p>');

       try{
        $this->mailer->send($email);
       } catch (\Exception $e) {
        dd($e->getMessage());
       }
    }

    public function codeGenerate(): int
    {

        $maintenant = new DateTime();
        $heureActuelle = $maintenant->format('G');  
        $minutesActuelles = $maintenant->format('i');

        $moisActuel = $maintenant->format('n'); 
        $jourActuel = $maintenant->format('j'); 


        return $heureActuelle*$minutesActuelles*3600+$moisActuel*$jourActuel*1200;
    }

}