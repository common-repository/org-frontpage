=== Organisation Frontpage ===
Contributors: bgin
Tags: frontpage, front page, theme, template, frontpage, magazine, post, themes, posts
Requires at least: 4.6
Tested up to: 6.2
Stable tag: 2.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Permet de choisir et agencer les posts de la frontpage.

== Description ==

Dans Wordpress il n'y a pas de procédure pour désigner les posts qui doivent figurer sur la frontpage des blogs, lorsque ceux-ci ont une page d'accueil statique.

**Ce plugin permet d'assigner à un post le rang qu'il doit occuper sur la page d'accueil.**

Pour cela, le plugin ajoute une page "OrgFrontpage" dans le menu "Réglages" du tableau de bord.

On peut y définir le nombre de posts à faire figurer sur la page d'accueil. Par défaut, ce nombre est fixé à 5.
Dans ce même sous-menu, il est possible de définir le nombre d'articles à mettre en évidence en tête de page. Par défaut, il y en a zéro.
(Remarque : Le nombre de posts défini pour figurer sur la page d'accueil inclut les posts d'entête).

Une fois ces paramètres définis, on peut choisir dans une liste déroulante les articles à afficher sur la page d'accueil. La liste contient, par défaut, les 15 derniers articles publiées. Ce nombre est paramétrable. 

De plus, le plugin ajoute une meta box dans la page d'édition des posts 
grâce à laquelle on peut choisir, au moyen d'une combolist, le rang du post en cours d'édition.

Enfin, une colonne a été ajoutée dans la liste des posts de l'interface d'administration :
elle indique, le cas échéant, le rang des posts sur la page d'accueil.

Les données associées à ce plugin sont stockées dans la table wp-options.
Les *option-name* sont :

* *ofp_nb_posts_frontpage* : nombre de posts à placer sur la page d'accueil
* *ofp_nb_posts_lead* : nombre de posts en évidence en tête de page
* *ofp_post_frontpage_N* : l'ID du post qui occupera le rang N de la page d'accueil


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Plugin Name screen to configure the plugin
1. Adapter le template de la frontpage du blog :

On peut par exemple insérer le code suivant :

    <?php
    
    $nb_posts_frontpage = intval(get_option('ofp_nb_posts_frontpage'));
    
    for ($i = 1; $i <= $nb_posts_frontpage; $i++) {
	$post_id_frontpage[$i] = get_option('ofp_post_frontpage_'.$i);
     }

    $nb_posts_lead = intval(get_option('ofp_nb_posts_lead'));
    
    ?>


== Frequently Asked Questions ==

= Ce plugin est-il nécessaire ? =

Je souhaitais choisir les posts à mettre sur la page d'accueil et les disposer selon leur niveau d'importance
et je n'ai pas trouvé le moyen de le faire simplement.

= Il existe un moyen simple de désigner les articles à mettre en avant (attribut sticky disponible dans le tableau de bord). Ne permet-il pas de faire la même chose sans plugin ? =

Hélas non !
On pourrait en effet donner l'attribut sticky aux posts qu'on souhaite mettre sur la page d'accueil, mais cela ne permet pas de les agencer à sa convenance sur cette page : on peut en effet les récupérer classés par date et les afficher dans cet ordre, mais il est impossible de les classer à son gré.
Le plugin org-frontpage permet une mise en page fine car il introduit un attribut de classement (Numéro 1, 2, 3,...) des posts sélectionnés. Ce classement pourra être exploité pour positionner les articles dans des emplacements dédiés au posts 1, 2, 3 ...

De plus, contrairement au système natif de mise en avant, les articles retenus pour la frontpage ne viennent pas automatiquement en tête des listes d'articles de type blog.
 

= Comment savoir quels sont les posts épinglés à la page d'accueil ? =

On peut évidemment les voir sur le blog, mais aussi à partir du tableau de bord, dans le sous-menu *Org frontpage* du menu *Réglages* . Le rang des posts choisis apparaît également dans une colonne supplémentaire le la liste de tous les articles.

= Pourquoi ce readme est-il en français ? =

Parce que mes compétences dans la langue de Shakespeare sont insuffisantes pour écrire une notice en anglais, sorry ;-)

== Screenshots ==

1. Ecran d'édition d'un post : montre la meta box qui peremet de choisir le rang du post sur la frontpage

2. Tableau de bord : montre l'accès au sous-menu *Org frontpage* du menu *Réglages*

3. Montre la page de réglages du plugin : on peut y modifier le nombre de posts de la frontpage

== Changelog ==

= 2.0.7 =
* Validation du plugin pour Wordpress 6.5 et php 8.1.

= 2.0.6 =
* Validation du plugin pour Wordpress 6.2.

= 2.0.4 =
* Validation du plugin pour Wordpress 5.6.

= 2.0.3 =
* Validation du plugin pour Wordpress 5.4.

= 2.0.2 =
* Validation du plugin pour Wordpress 5.1.

= 2.0.1 =
* Retouche pour éviter les doublons sur la page d'accueil.

= 2.0 =
* Améliartion de l'ergonomie. Le choix de tous les articles à ranger sur la page d'accueil peut désormais se réaliser à un endroit unique, la page des réglages du plugin. 
Toutefois, le choix de l'emplacement d'un article reste possible à partir de la page d'édition de celui-ci.

= 1.6 =
* Correction d'un bug : l'affichage de la liste des articles de la page d'accueil, présente sur la page d'édition d'un article, provoquait un warning lorsqu'un article de cette liste avait été supprimé.

= 1.5 =
* Correction d'un dysfonctionnement : lorsque on procède à la mise à jour d'un post marqué pour la page d'accueil, la sélection était perdue et il fallait la rétablir.

= 1.4.7 =
* Correction d'un bug

= 1.4.1 =
* Correction d'un bug

= 1.4 =
* Réécriture du plugin en programmation objet
* Langage natif : anglais US
* Traduction en français fr_FR 

= 1.3.3 =
* Correction de bugs / traduction

= 1.3.1 =
* Modification et déplacement des fichiers de traduction

= 1.3 =
* Traduction du plugin en anglais (english US)

= 1.2 =
* Ajout d'un paramètre pour indiquer, le cas échéant, le nombre de posts à mettre en évidence en tête de page

= 1.1 =
* Ajout d'une colonne dans la liste des posts, pour indiquer le rang des posts sur la frontpage

= 1.0.1 =
* Ajout de screenshots
* Suppression d'un répertoire inutile
* amélioration de readme.txt

= 1.0 =
* First version.

