<?php
/*
Plugin Name: Organisation FrontPage
Description: Adding a meta box in the post editor to set the rank of post in the home page
Version: 2.0.7
Author:      B Gineste
Text Domain: org-frontpage
Domain Path: /languages
Requires at least: 4.6
License:     GPLv2 or later
*/
?><?php
$en_test = false;

class org_frontpage {
// constantes
	private $_nb_posts_frontpage_std = 5; //nombre de posts à afficher en page d'accueil
	private $_nb_posts_lead_std = 0; //nombre de posts en entête en page d'accueil
//variables globales
	// Nombre de posts en frontpage (méta donnée dans wp_options)
	private $_nb_posts_frontpage;
	// nombre de posts en entête en page d'accueil
	private $_nb_posts_lead;
	// tableau des noms des metadonnées de la table wp_options (option_name)
	private $_option_names = array();
	// tableau des valeurs dans wp_options (option_value)
	private $_option_values = array();
	// tableau des libellés (pour la saisie dans la méta box)
	private $_libelles = array();
	//fichier mouchard-org-fpg
	private $_f = null;
	private $_trace = false;
	
	function __construct() {

		add_action( 'plugins_loaded', array($this,'org_frontpage_load_plugin_textdomain') );

		//ouverture du mouchard
		if ($this->_trace) { $this->_f=fopen($_SERVER['DOCUMENT_ROOT'].'\mouchard-org-fpg.txt','a+'); }
		//définition des variables globales
			// Nombre de posts en frontpage (méta donnée dans wp_options)
			if (!get_option('ofp_nb_posts_frontpage')) {update_option('ofp_nb_posts_frontpage',$this->_nb_posts_frontpage_std);}
			$this->_nb_posts_frontpage = get_option('ofp_nb_posts_frontpage');
			if (!$this->_nb_posts_frontpage) {
				$this->_nb_posts_frontpage = $this->_nb_posts_frontpage_std;
				update_option('ofp_nb_posts_frontpage',$this->_nb_posts_frontpage);
			}
			// nombre de posts en entête en page d'accueil
			if (!get_option('ofp_nb_posts_lead')) {update_option('ofp_nb_posts_lead',$this->_nb_posts_lead_std);}
			$this->_nb_posts_lead = get_option('ofp_nb_posts_lead');
			if (!$this->_nb_posts_lead) {
				$this->_nb_posts_lead = $this->_nb_posts_lead_std;
				update_option('ofp_nb_posts_lead',$this->_nb_posts_lead);
			}
			// tableau des noms des metadonnées de la table wp_options (option_name)
			for ($i=1;$i<=$this->_nb_posts_frontpage;$i++) {$this->_option_names[$i] = "ofp_post_frontpage_".$i; } 
			// tableau des valeurs dans wp_options (option_value)
			foreach ($this->_option_names as $option_name) {
				//après une mise à jour du post, l'option_value prend la valeur de l'ID du post révision, il faut rétablir l'ID du post parent
				//cet inconvénient  ne semble pouvoir être évité, même si les options ont 'no' comme valeur du champ autoload
				
				$thepost_id = get_option($option_name);
				$thepost = get_post($thepost_id);
				//var_dump($thepost_id);
				if (!is_null($thepost)){ 
				if ($thepost->post_parent > 0) {
					$thepost_revision_id = $thepost_id;
					$thepost_id = $thepost->post_parent; 
					update_option($option_name,$thepost_id); 
					if (!is_null($this->_f)) { 
						fwrite($this->_f,"Rétabli post parent"."\r\n");
						fwrite($this->_f,$thepost_revision_id." ".$thepost->post_parent."\r\n");
					}
				}
				}
				$this->_option_values[$option_name]=$thepost_id;
				}
			// tableau des libellés (pour la saisie dans la méta box) : initialisé au moment de l'utiliser  (fonction les_libelles) : ici, il n'est pas traduit

			add_action('add_meta_boxes', array($this,'rang_post_meta_box_add' ));
			add_action('save_post', array($this,'rang_post_meta_box_save' ));
			add_action('manage_posts_custom_column', array($this,'data_colonne'));
			add_action('admin_menu',array($this,'org_frontpage_plugin_menu'));
			add_filter('manage_posts_columns' , array($this,'ofp_colonne'));

		}
		
	function org_frontpage_load_plugin_textdomain() {
		load_plugin_textdomain( 'org-frontpage', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	private function generer_les_libelles() {
	// tableau des libellés (pour la saisie dans la combobox de la méta box)
		
		$this->_libelles["pas_sur_frontpage"]=__("Not on the homepage", 'org-frontpage' ); 
		$i=1;
		foreach ($this->_option_names as $option_name) {
			$this->_libelles[$option_name]=__("#", 'org-frontpage' ).$i." / ".__("Home Page", 'org-frontpage' ); $i++;} 
//		return $libelles;
	}

	function __destruct() {
		if (!is_null($this->_f)) { fclose($this->_f); }
	}
/*
============================================================================================================================
Ajout de la meta box
============================================================================================================================
*/


function rang_post_meta_box_add()
{
    add_meta_box( 'rang_post', __( 'Location on home page', 'org-frontpage' ), array($this,'rang_post_meta_box_callback'), 'post' );
}

/*
Rendering de la meta box
*/
function rang_post_meta_box_callback($post) 
{
//global $option_values;
//Rang de l'article (défini par le nom de la méta donnée de wp-options)
	$this->generer_les_libelles();
	// déterminer le rang de l'article sur la frontpage (par son option_name)
	// ici on pourrait vérifier que le post n'a pas de parent. Et le cas échéant remplacer l'ID du post par l'ID du parent (semble inutile)
	//$post_id = $post->ID;
	//if ($post->post_parent > 0) {$post_id = $post->post_parent; }
	//$rang_post = array_search($post_id,$this->_option_values);
	$rang_post = array_search($post->ID,$this->_option_values);
	
	if ($rang_post == false) {$rang_post="pas_sur_frontpage";}
	
	?>
	<div id="box-org-frontpage" style="display: flex; flex-wrap: wrap;">
		<div class="form-field">
			<form method="post">
			<?php 
				// We'll use this nonce field later on when saving.
				 wp_nonce_field( basename( __FILE__ ), 'organisation_frontpage' );
				 //echo $post->ID; echo " - "; echo $post_id; echo " - "; echo $rang_post;
			?>
			<label for="rang_frontpage"><?php _e( 'Post location', 'org-frontpage' ); ?> </label>
			<select  name="rang_frontpage" id="rang_frontpage">
			<?php foreach ($this->_libelles as $libelle) { ?><option<?php if ( array_search($libelle,$this->_libelles) == $rang_post) { echo ' selected="selected"'; } ?>><?php echo $libelle; ?></option>
			<?php } ?>
			</select>
			</form>
		</div>
		<div style="margin-left: 20px;">
		<span style="font-weight: bold; font-size: 130%;"> <?php _e('Posts currently selected','org-frontpage'); ?></span>
		<table class="optiontable"> 
		<?php
		// Affichage des articles de la page d'accueil
			for ($i=1;$i<=$this->_nb_posts_frontpage;$i++) {
				$nom_option = "ofp_post_frontpage_".$i; 
				$post_id = get_option($nom_option);
				$titre = "Not set";
				if ($post_id != false) {
					//echo "***"; echo $post_id; echo "***<br />";
					$article = get_post( $post_id );
					if (!is_null($article)) {
						$titre = $article->post_title;
					}
				}
		?> 
		<!--Articles -->
		<tr valign="top">
		<th scope="row"><?php _e("Rank", 'org-frontpage'); echo " ".$i ; ?> : </th><td style="background-color: #FFFFFF;"><?php _e($titre, 'org-frontpage'); ?></td>
		</tr>
				<?php // } else { ?>
		<!-- <tr valign="top">
		<th scope="row"><?php _e("Rank", 'org-frontpage'); echo " ".$i ; ?> : </th><td style="background-color: #FFFFFF;"><?php _e("Not set", 'org-frontpage'); ?></td>
		</tr> -->
			<?php } ?>

		</table>
		</div>
	</div>
	<?php
}

/*
Saving de la meta box
*/


function rang_post_meta_box_save( $post_id )
{
	if (!is_null($this->_f)) { fwrite($this->_f,"\r\n"."Début séquence save ".date('d/m/Y H:i')."\r\n"); }

	$this->generer_les_libelles();
    // Bail if we're doing an auto save
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    // if our nonce isn't there, or we can't verify it, bail
   if( !isset( $_POST['organisation_frontpage'] ) || !wp_verify_nonce( $_POST['organisation_frontpage'], basename( __FILE__ ) ) ) return;
     
    // if our current user can't edit this post, bail
    if( !current_user_can( 'edit_posts' ) ) return;

    // Make sure your data is set before trying to save it
    if( isset( $_POST['rang_frontpage'] ) ) {
		$libelle_frontpage=stripslashes($_POST['rang_frontpage']);
		// rang du post sur la frontpage
		$name_option_post = array_search($libelle_frontpage,$this->_libelles);
		// déterminer le rang précédent de l'article sur la frontpage
		$name_option_post_prec = array_search($post_id,$this->_option_values);
		if(!is_null($this->_f)){
			fwrite($this->_f, "name_option_post_prec (devrait être false si pas sur la frontpage) :");
			fwrite($this->_f,$name_option_post_prec."\r\n");
		}
		if (empty($name_option_post_prec)) {$name_option_post_rec="pas_sur_frontpage";}
		if (!is_null($this->_f)) {
			
			fwrite($this->_f, "Post ID :");
			fwrite($this->_f,$post_id."\r\n");
			
			fwrite($this->_f, "libelle_frontpage :");
			fwrite($this->_f,$libelle_frontpage."\r\n");
			fwrite($this->_f,"name_option_post :");
			fwrite($this->_f,$name_option_post."\r\n");
			fwrite($this->_f,"name_option_post_prec :");
			fwrite($this->_f,$name_option_post_prec."\r\n");
			fwrite($this->_f,"Id des stickés (courant ".$post_id.") : "."\r\n");
		
			//var_dump(get_post($post_id));
			foreach($this->_option_values as $opt) {
				$article = get_post( $opt ); 
				fwrite($this->_f,$article->post_title." - ");
				fwrite($this->_f,$opt."\r\n");
				}
			
			foreach($this->_libelles as $lib) {
				fwrite($this->_f,$lib."\r\n");			
			}
		}
		
		if ($name_option_post != $name_option_post_prec) {
			if ($name_option_post_prec == "pas_sur_frontpage") {
				update_option($name_option_post,$post_id);
					if (!is_null($this->_f)) {
						fwrite($this->_f,"UPDATE name_option_post :"."\r\n");
						fwrite($this->_f,$name_option_post.' : '.$post_id."\r\n");
					}
}
			if ($name_option_post == "pas_sur_frontpage") {
				update_option($name_option_post_prec,FALSE);
					if (!is_null($this->_f)) {
						fwrite($this->_f,"UPDATE name_option_post_prec :"."\r\n");
						fwrite($this->_f,$name_option_post_prec.' : false'."\r\n");
					}
				}
			if ($name_option_post_prec != "pas_sur_frontpage" && $name_option_post != "pas_sur_frontpage") {
				update_option($name_option_post_prec,FALSE);
				update_option($name_option_post,$post_id);
					if (!is_null($this->_f)) {
						fwrite($this->_f,"UPDATE name_option_post_prec :"."\r\n");
						fwrite($this->_f,$name_option_post_prec.' : false'."\r\n");
						fwrite($this->_f,"UPDATE name_option_post :"."\r\n");
						fwrite($this->_f,$name_option_post.' : '.$post_id."\r\n");
					}
			}
			
		}			
	}
		if (!is_null($this->_f)) {
			fwrite($this->_f, "Post ID :");
			fwrite($this->_f,$post_id."\r\n");
			foreach($this->_option_values as $opt) {
				$article = get_post( $opt ); 
				fwrite($this->_f,$article->post_title." - ");
				fwrite($this->_f,$opt."\r\n");
			}
		}
//	fclose($f);     	
}

/*
============================================================================================================================
Ajout d'une colonne dans la liste des posts (pour indiquer dans cette liste le rang des posts sur la frontpage)
============================================================================================================================
*/
function ofp_colonne($columns) {
 return array_merge( $columns, 
 array('rang_frontpage' => __('Rank / home page','org-frontpage')) );
}


// Affichage des données

function data_colonne($name) {
 global $post; 
// $this->generer_les_libelles();
 switch ($name) {
case 'rang_frontpage':
//Rang de l'article (défini par le nom de la méta donnée de wp-options)

	// déterminer le rang de l'article sur la frontpage (par son option_name)
	$rang_post = array_search(array_search($post->ID,$this->_option_values),$this->_option_names);
	if ($rang_post == false) {$rang_post="---";}
 
	echo "$rang_post";
    break;
 }
 }

/* 
============================================================================================================================
Administration du plugin
============================================================================================================================
*/
function org_frontpage_plugin_menu(){
	add_options_page(__('Front page organization','org-frontpage'), __('Org Frontpage','org-frontpage'), 'manage_options', 'org-frontpage-menu', array($this,'org_frontpage_plugin_options')); 
} 


function org_frontpage_plugin_options(){ 
	include('org-frontpage-plugin-admin.php'); 
}

}

new org_frontpage();

?><?php
if ($en_test) {
// Désactiver le rapport d'erreurs
error_reporting(0);

// Rapports erreurs qui se produisent lors de l'exécution du script
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Le rapport E_NOTICE peut vous aider à améliorer vos scripts
// (uninitialised variables, misspelt variables etc)
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

// Répertorie toutes les erreurs en dehors de E_NOTICEs
// This is the default configuration for php.ini
error_reporting(E_ALL ^ E_NOTICE);

// Rapports de toute les erreurs PHP
error_reporting(E_ALL);

// Même chose que error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
}
?>