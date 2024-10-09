<?php
// src/Controller/PegiController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

require_once 'modele/class.PdoAgora.inc.php';

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use PdoAgora;

class PegiController extends AbstractController
{
    /**
     * fonction pour afficher la liste des Pegi
     * @param $db
     * @param $idPegiModif positionné si demande de modification
     * @param $idPegiNotif positionné si mise à jour dans la vue
     * @param $notification pour notifier la mise à jour dans la vue
     */
    private function afficherPegi(
        PdoAgora $db,
        int $idPegiModif,
        int $idPegiNotif,
        string $notification
    ) {
        $tbMembres = $db->getLesMembres();
        $tbPegi = $db->getLesPegiComplet();
        return $this->render('lesPegi.html.twig', array(
            'menuActif' => 'Jeux',
            'tbPegi' => $tbPegi,
            'tbMembres' => $tbMembres,
            'idPegiModif' => $idPegiModif,
            'idPegiNotif' => $idPegiNotif,
            'notification' => $notification
        ));
    }

    #[Route('/pegi', name: 'pegi_afficher')]

    public function index(SessionInterface $session)
    {
        if ($session->has('idUtilisateur')) {
            $db = PdoAgora::getPdoAgora();
            return $this->afficherPegi($db::getPdoAgora(), -1, -1, 'rien');
        } else {
            return $this->render('connexion.html.twig');
        }
    }
    #[Route('/pegi/ajouter', name: 'pegi_ajouter')]
    public function ajouter(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        if (!empty($request->request->get('txtdescPegi'))) {
            $idPegiNotif = $db->ajouterPegi(
                $request->request->get('txtageLimite'),
                $request->request->get('txtdescPegi'),
                $request->request->get('lstMembre')
            );
            $notification = 'Ajouté';
        }
        return $this->afficherPegi($db::getPdoAgora(), -1, $idPegiNotif, $notification);
    }
    #[Route('/pegi/demandermodifier', name: 'pegi_demandermodifier')]
    public function demanderModifier(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        return $this->afficherPegi($db::getPdoAgora(), $request->request->get('txtidPegi'), -1,'rien');
    }
    #[Route('/pegi/validermodifier', name: 'pegi_validermodifier')]
    public function validerModifier(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        $db->modifierPegi($request->request->get('txtidPegi'), $request->request->get('txtageLimite'), $request->request->get('txtdescPegi'), $request->request->get('lstMembre'));
        return $this->afficherPegi($db::getPdoAgora(), -1, $request->request->get('txtidPegi'), 'Modifié');
    }
    #[Route('/pegi/supprimer', name: 'pegi_supprimer')]
    public function supprimer(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        $db->supprimerPegi($request->request->get('txtidPegi'));
        $this->addFlash(
            'success',
            'Le pegi a été supprimé'
        );
        return $this->afficherPegi($db::getPdoAgora(), -1, -1, 'rien');
    }
}
