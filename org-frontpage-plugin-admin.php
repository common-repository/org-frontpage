<?php
// Inclus dans org-frontpage.php
// Options de la page d'accueil (frontpage)


//Définir la valeur par défaut du nombre d'articles à proposer pour la front page
if (empty($_SESSION['nbposts'])) {
	$_SESSION['nbposts'] = 15; 
}

//Si on a modifié le nombre de posts à afficher dans la liste déroulante
if (isset($_POST['nb_posts_a_choisir'])) {
	$_SESSION['nbposts'] = $_POST['nb_posts_a_choisir'];
}

// table des derniers articles 
$p=0;
$arts=array();

global $args;
$args = array('numberposts' => $_SESSION['nbposts']);
global $post;

$lesposts = get_posts( $args );
foreach ( $lesposts as $post ) : setup_postdata( $post );
	$arts[$p]=array('ID' => $post->ID, 'Titre' => $post->post_title);
	$p++;
endforeach; 
wp_reset_postdata();


// Pourquoi utiliser$_request au lieu de $_post, puisqu'on sait que la méthode est "post" ?
if (isset($_REQUEST['save'] )) {
		if( !isset( $_POST['nb_posts_frontpage_nonce'] ) || !wp_verify_nonce( $_POST['nb_posts_frontpage_nonce'], basename( __FILE__ ).'_truc' ) ){
				_e("Action denied", 'org-frontpage');
		}
		else {
		// Mise à jour des paramètres
		if ($_REQUEST[ 'nb_posts_frontpage' ]>0) {
			update_option( 'ofp_nb_posts_frontpage', $_REQUEST[ 'nb_posts_frontpage' ] );
			$this->_nb_posts_frontpage = $_REQUEST[ 'nb_posts_frontpage' ];
		}
		if ($_REQUEST[ 'nb_posts_lead' ]>-1) {
			update_option( 'ofp_nb_posts_lead', $_REQUEST[ 'nb_posts_lead' ] );
			$this->_nb_posts_lead = $_REQUEST[ 'nb_posts_lead' ];
		}
		}
		unset($_REQUEST['save'] ); // remplacé 'action' par 'save' le 7/12/2017 ... à surveiller
		
}

// On récupère le Rang de l'article éventuellement modifié
if (!empty($this->_nb_posts_frontpage)) {
$article = -1;

	for ($i = 1; $i <= $this->_nb_posts_frontpage; $i++) {
		if (isset($_POST['article-'.$i])) {
			$article = intval(htmlspecialchars($_POST['article-'.$i]));
			$rg = $i;
			break;
		}
	}
	if ($article > -1) { // il y a eu une demande de modification pour le rang $rg
		// ID de l'article prposé pour le rang $rg
		$newID = $arts[$article]['ID'];
		//vérifier si l'article assigné à ce rang a changé
		$oldID = get_option("ofp_post_frontpage_".$rg);
		if ($newID != $oldID) {
			// on resete l'assignation de cet article (s'il est déjà assigné) 
			for ($i=1;$i<=$this->_nb_posts_frontpage;$i++) {
				$nom_option = "ofp_post_frontpage_".$i; 
				$post_id = get_option($nom_option);
				if ($post_id == $newID) {
					update_option($nom_option,FALSE);
					break;
				}
			}
			update_option("ofp_post_frontpage_".$rg, $newID);
		}
	}
}

?>


<div class="wrap"><h1><?php _e("Homepage Settings", 'org-frontpage'); ?></h1>
<form method="post">

<h3><?php _e("Posts on the home page", 'org-frontpage'); ?></h3>

<!-- Saisie du nombre d'articles de la Une -->

<?php
	wp_nonce_field( basename( __FILE__ ).'_truc', 'nb_posts_frontpage_nonce' );
?>

<table class="optiontable">
<tr valign="center"><th scope="row"><?php _e("Total number of posts on the home page", 'org-frontpage'); ?>:</th><td>
<input type="number" name="nb_posts_frontpage" id="nb_posts_frontpage" min="1" value="<?php echo $this->_nb_posts_frontpage; ?>">
</td></tr>
<tr valign="center"><th scope="row"><?php _e("Number of posts at the top of the home page", 'org-frontpage'); ?>:</th><td>
<input type="number" name="nb_posts_lead" id="nb_posts_lead" min="0" value="<?php echo $this->_nb_posts_lead; ?>">
</td></tr>
</table>
<p class="submit"><input name="save" type="submit" value="<?php _e("Save changes",'org-frontpage'); ?>" /></p>
</form>

<h3><?php _e("Selection of posts", 'org-frontpage'); ?></h3>

<p><?php _e("The posts displayed on the home page are summarized below",'org-frontpage'); ?><br />
<?php _e("The selection of these posts can be done on this page: it also takes place in the edit page of the post",'org-frontpage'); ?><br /></p>

<form method="post">
	<label><?php _e("Nombre d'articles de la liste de choix") ?> </label>
	<input type="number" name="nb_posts_a_choisir" min="3" value="<?php echo $_SESSION['nbposts']; ?>" />
	<input name="save_nb_choix" type="submit" value="<?php _e("Save changes",'org-frontpage'); ?>" />
</form>

<?php
//var_dump($arts);

// Affichage  et modification des articles de la page d'accueil
	for ($i=1;$i<=$this->_nb_posts_frontpage;$i++) {
		$nom_option = "ofp_post_frontpage_".$i; 
		$post_id = get_option($nom_option);
		$titre='';
		$article=null;
		if (!empty($post_id)) {
			$article = get_post( $post_id );
		}
		if (!empty($article)) {
			$titre = $article->post_title;
		}
?> 
<!--Articles -->
<div style="margin: 18px 0; padding: 6px 0; background-color: #CCCCCC;" >
<form method="post">
<table class="optiontable"> 
<tr valign="center">
<th scope="row"><?php _e("Rank", 'org-frontpage'); echo " ".$i ; ?> : </th><td style="background-color: #FFFFFF;">
<?php if($titre=="") { _e("Not set", 'org-frontpage'); } else { echo $titre;} ?></td>
<td>
  <select id="article-<?php echo $i; ?>" name="article-<?php echo $i; ?>" style="max-width: 100%;">
  <?php
  $a=0;
  foreach($arts as $art) {
	  ?>
	  <option value="<?php echo $a; $a++; ?>"><?php echo $art['Titre'] ; ?></option>
  <?php	
  }
  ?>
  </select>
</td>
</tr>
</table>
<input name="modif-<?php echo $i; ?>" type="submit" value="<?php _e("Modify post range",'org-frontpage'); echo' '; echo $i; ?>" />
</form>
</div>
	<?php } ?>



