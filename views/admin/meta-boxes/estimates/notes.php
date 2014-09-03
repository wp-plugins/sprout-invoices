<?php 
	$editor_settings = array(
			'media_buttons'     => FALSE,
			'textarea_rows'     => 4,
		);
 ?>

<h3><?php si_e('Terms') ?> <span class="helptip" title="<?php si_e("Terms will be shown on the estimate.") ?>"></span></h3>
<?php
	wp_editor( $terms, 'estimate_terms', $editor_settings ); ?>

<h3><?php si_e('Notes') ?> <span class="helptip" title="<?php si_e("These notes will be shown on the estimate.") ?>"></span></h3>
<?php
	wp_editor( $notes, 'estimate_notes', $editor_settings ); ?>
