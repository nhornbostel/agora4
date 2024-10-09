<?php
// src/Controller/MarquesController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

require_once 'modele/class.PdoAgora.inc.php';

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use PdoAgora;

class MarquesController extends AbstractController
{
    /**
     * fonction pour afficher la liste des Marques
     * @param $db
     * @param $idMarqueModif positionné si demande de modification
     * @param $idMarqueNotif positionné si mise à jour dans la vue
     * @param $notification pour notifier la mise à jour dans la vue
     */
    private function afficherMarques(
        PdoAgora $db,
        int $idMarqueModif,
        int $idMarqueNotif,
        string $notification
    ) {
        $tbMembres = $db->getLesMembres();
        $tbMarques = $db->getLesMarquesComplet();
        return $this->render('lesMarques.html.twig', array(
            'menuActif' => 'Jeux',
            'tbMarques' => $tbMarques,
            'tbMembres' => $tbMembres,
            'idMarqueModif' => $idMarqueModif,
            'idMarqueNotif' => $idMarqueNotif,
            'notification' => $notification
        ));
    }

    #[Route('/marques', name: 'marques_afficher')]

    public function index(SessionInterface $session)
    {
        if ($session->has('idUtilisateur')) {
            $db = PdoAgora::getPdoAgora();
            return $this->afficherMarques($db::getPdoAgora(), -1, -1, 'rien');
        } else {
            return $this->render('connexion.html.twig');
        }
    }
    #[Route('/marques/ajouter', name: 'marques_ajouter')]
    public function ajouter(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        if (!empty($request->request->get('txtNomMarque'))) {
            $idMarqueNotif = $db->ajouterMarque(
                $request->request->get('txtNomMarque'),
                $request->request->get('lstMembre')
            );
            $notification = 'Ajouté';
        }
        return $this->afficherMarques($db::getPdoAgora(), -1, $idMarqueNotif, $notification);
    }
    #[Route('/marques/demandermodifier', name: 'marques_demandermodifier')]
    public function demanderModifier(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        return $this->afficherMarques($db::getPdoAgora(), $request->request->get('txtIdMarque'), -1,'rien');
    }
    #[Route('/marques/validermodifier', name: 'marques_validermodifier')]
    public function validerModifier(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        $db->modifierMarque($request->request->get('txtIdMarque'), $request->request->get('txtNomMarque'), $request->request->get('lstMembre'));
        return $this->afficherMarques($db::getPdoAgora(), -1, $request->request->get('txtIdMarque'), 'Modifié');
    }
    #[Route('/marques/supprimer', name: 'marques_supprimer')]
    public function supprimer(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        $db->supprimerMarque($request->request->get('txtIdMarque'));
        $this->addFlash(
            'success',
            'Le Marque a été supprimé'
        );
        return $this->afficherMarques($db::getPdoAgora(), -1, -1, 'rien');
    }
}
