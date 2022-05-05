# CHANGELOG DISTRIBUTIONLIST FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

## Unreleased



## 1.3

- FIX : lors de la suppression d'un contact, il faut décrémenter le nombre de contact dans les listes de diffusion concernés - *05/05/2022* - 1.3.3
- FIX : Test d'unicité sur le label était non fonctionnel - *20/04/2022* - 1.3.2
- FIX : Récupération des listes de diffusion au statut clôturé dans le select des listes de diffusions sur l'onglet "Destinataires" d'une campagne - *08/03/2022* - 1.3.1
- FIX : Empêcher les doublons :
  - Conf DISTRIBUTIONLISTUNIQUELABEL : Rendre le libellé des listes de diffusion unique + message d'erreur et lien vers la liste existante avec le même nom
  - Onglet ajout des destinataires, afficher les filtres personnalisés par ordre alphabétique + empecher les doublons
  - Dans Sarbacane, onglet Destinataires, afficher la liste des LDD par ordre alphabétique - *02/02/2021* - 1.3.0

## 1.2

- FIX : Suppression des liste de diffusion au statut brouillon des liste de liste de diffusion - *25/02/2022* - 1.2.4
- FIX : Bouton "Enregistrer filtre" ne faisait pas la bonne action - *21/12/2021* - 1.2.3
- FIX : Correction "Ajouter contacts à la liste" : maintenir le filtre si on change le nombre de contacts à afficher - *08/12/2021* - 1.2.2
- FIX : Correction de l'action d'ajout de tous les contacts sur la liste de diffusion - *06/12/2021* - 1.2.1
- NEW : Bouton action ajouter tous les contacts filtrés - *07/10/2021* - 1.2.0
- FIX : Insertion des contacts en pur sql. Le probleme venait du volume de donnees et du fait qu'on parcourait 2 fois un tableau de 27000 lignes... timeout - *01/09/2021* - 1.1.5
- FIX : Transfert fonctionnalité "Ajout d'une liste de diffusion dans les destinataires d'un mailing" + adaptation de la méthode dolibarr - *21/07/2021* - 1.1.4
- FIX : Suppression du champ date cloture dans popup de confirmation + MAJ date cloture sur cloture - *15/06/2021* - 1.1.3 
- FIX : Impossibilité de valider une liste de diffusion lorsqu'elle est vide - *15/06/2021* - 1.1.2
- FIX : Ajout du champ "Date de cloture" sur formulaire de creation - *14/06/2021* - 1.1.1
- NEW : Séparation des permissions de création/modification - *02/06/2021* - 1.1.0

## 1.0

Initial version
