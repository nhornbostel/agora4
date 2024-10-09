<?php
// src/Controller/PlateformesController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

require_once 'modele/class.PdoAgora.inc.php';

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use PdoAgora;

class PlateformesController extends AbstractController
{
    /**
     * fonction pour afficher la liste des plateformes
     * @param $db
     * @param $idPlateformeModif positionné si demande de modification
     * @param $idPlateformeNotif positionné si mise à jour dans la vue
     * @param $notification pour notifier la mise à jour dans la vue
     */
    private function afficherPlateformes(
        PdoAgora $db,
        int $idPlateformeModif,
        int $idPlateformeNotif,
        string $notification
    ) {
        $tbMembres = $db->getLesMembres();
        $tbPlateformes = $db->getLesPlateformesComplet();
        return $this->render('lesPlateformes.html.twig', array(
            'menuActif' => 'Jeux',
            'tbPlateformes' => $tbPlateformes,
            'tbMembres' => $tbMembres,
            'idPlateformeModif' => $idPlateformeModif,
            'idPlateformeNotif' => $idPlateformeNotif,
            'notification' => $notification
        ));
    }

    #[Route('/plateformes', name: 'plateformes_afficher')]

    public function index(SessionInterface $session)
    {
        if ($session->has('idUtilisateur')) {
            $db = PdoAgora::getPdoAgora();
            return $this->afficherPlateformes($db::getPdoAgora(), -1, -1, 'rien');
        } else {
            return $this->render('connexion.html.twig');
        }
    }
    #[Route('/plateformes/ajouter', name: 'plateformes_ajouter')]
    public function ajouter(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        if (!empty($request->request->get('txtLibPlateforme'))) {
            $idPlateformeNotif = $db->ajouterPlateforme(
                $request->request->get('txtLibPlateforme'),
                $request->request->get('lstMembre')
            );
            $notification = 'Ajouté';
        }
        return $this->afficherPlateformes($db::getPdoAgora(), -1, $idPlateformeNotif, $notification);
    }
    #[Route('/plateformes/demandermodifier', name: 'plateformes_demandermodifier')]
    public function demanderModifier(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        return $this->afficherPlateformes($db::getPdoAgora(), $request->request->get('txtIdPlateforme'), -1,'rien');
    }
    #[Route('/plateformes/validermodifier', name: 'plateformes_validermodifier')]
    public function validerModifier(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        $db->modifierPlateforme($request->request->get('txtIdPlateforme'), $request->request->get('txtLibPlateforme'), $request->request->get('lstMembre'));
        return $this->afficherPlateformes($db::getPdoAgora(), -1, $request->request->get('txtIdPlateforme'), 'Modifié');
    }
    #[Route('/plateformes/supprimer', name: 'plateformes_supprimer')]
    public function supprimer(SessionInterface $session, Request $request)
    {
        $db = PdoAgora::getPdoAgora();
        $db->supprimerPlateforme($request->request->get('txtIdPlateforme'));
        $this->addFlash(
            'success',
            'La plateforme a été supprimée'
        );
        return $this->afficherPlateformes($db::getPdoAgora(), -1, -1, 'rien');
    }
}
