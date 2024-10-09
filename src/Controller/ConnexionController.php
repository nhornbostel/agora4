<?php
// src/Controller/ConnexionController.php
namespace App\Controller;

require_once 'modele/class.PdoAgora.inc.php';

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use PdoAgora;

class ConnexionController extends AbstractController
{

    #[Route('/connexion/valider', name: 'connexion_valider')]

    public function validerConnexion(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        $utilisateur = $db->getUnMembre($request->request->get('txtLogin'), $request->request->get('hdMdp'));
        // si l'utilisateur n'existe pas
        if (!$utilisateur) {
            // positionner le message d'erreur
            $this->addFlash(
                'danger',
                'Login ou mot de passe incorrect !'
            );
            return $this->render('connexion.html.twig');
        } else {
            // créer trois variables de session pour id utilisateur, nom et prénom
            $session->set('idUtilisateur', $utilisateur->idMembre);
            $session->set('nomUtilisateur', $utilisateur->nomMembre);
            $session->set('prenomUtilisateur', $utilisateur->prenomMembre);
            // redirection du navigateur vers la page d'accueil
            return $this->redirectToRoute('accueil');
        }
    }
    #[Route('/deconnexion', name: 'deconnexion')]
    public function deconnexion(SessionInterface $session)
    {
        // supprimer la session
        $session->clear();
        $session->invalidate();
        // redirection vers l'accueil
        return $this->redirectToRoute('accueil');
    }
}
