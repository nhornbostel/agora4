<?php
// src/Controller/JeuxController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

require_once 'modele/class.PdoAgora.inc.php';

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use PdoAgora;

class JeuxController extends AbstractController
{
    /**
     * fonction pour afficher la liste des Jeux
     * @param $db
     * @param $refJeuModif positionné si demande de modification
     * @param $refJeuNotif positionné si mise à jour dans la vue
     * @param $notification pour notifier la mise à jour dans la vue
     */
    private function afficherJeux(
        PdoAgora $db,
        int $refJeuModif,
        int $refJeuNotif,
        string $notification
    ) {
        $tbMembres = $db->getLesMembres();
        $tbJeux = $db->getLesJeuxComplet();
        return $this->render('lesJeux.html.twig', array(
            'menuActif' => 'Jeux',
            'tbJeux' => $tbJeux,
            'tbMembres' => $tbMembres,
            'refJeuModif' => $refJeuModif,
            'refJeuNotif' => $refJeuNotif,
            'notification' => $notification
        ));
    }

    #[Route('/jeux', name: 'jeux_afficher')]

    public function index(SessionInterface $session)
    {
        if ($session->has('idUtilisateur')) {
            $db = PdoAgora::getPdoAgora();
            return $this->afficherJeux($db::getPdoAgora(), -1, -1, 'rien');
        } else {
            return $this->render('connexion.html.twig');
        }
    }
    #[Route('/jeux/ajouter', name: 'jeux_ajouter')]
    public function ajouter(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        if (!empty($request->request->get('txtnomJeu'))) {
            $refJeuNotif = $db->ajouterJeu(
                $request->request->get('txtnomJeu'),
                $request->request->get('lstMembre')
            );
            $notification = 'Ajouté';
        }
        return $this->afficherJeux($db::getPdoAgora(), -1, $refJeuNotif, $notification);
    }
    #[Route('/jeux/demandermodifier', name: 'jeux_demandermodifier')]
    public function demanderModifier(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        return $this->afficherJeux($db::getPdoAgora(), $request->request->get('txtrefJeu'), -1,'rien');
    }
    #[Route('/jeux/validermodifier', name: 'jeux_validermodifier')]
    public function validerModifier(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        $db->modifierJeu($request->request->get('txtrefJeu'), $request->request->get('txtnomJeu'), $request->request->get('lstMembre'));
        return $this->afficherJeux($db::getPdoAgora(), -1, $request->request->get('txtrefJeu'), 'Modifié');
    }
    #[Route('/jeux/supprimer', name: 'jeux_supprimer')]
    public function supprimer(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        $db->supprimerJeu($request->request->get('txtrefJeu'));
        $this->addFlash(
            'success',
            'Le jeu a été supprimé'
        );
        return $this->afficherJeux($db::getPdoAgora(), -1, -1, 'rien');
    }
}
