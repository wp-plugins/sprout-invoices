<dl id="history_list">

	<dt>
		<span class="history_status creation_event"><?php si_e('Created') ?></span><br/>
		<span class="history_date"><?php echo date_i18n( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), strtotime( $post->post_date ) ) ?></span>
	</dt>

	<dd><p>
		<?php if ( !empty( $submission_fields ) ): ?>
			<?php if ( $estimate->get_client_id() ): ?>
				<?php printf( si__('Submitted by <a href="%s">%s</a>'), get_edit_post_link( $estimate->get_client_id() ), get_the_title( $estimate->get_client_id() ) ) ?>
			<?php else: ?>
				<?php si_e('Submitted') ?>
			<?php endif ?>
		<?php elseif( is_a( $post, 'WP_Post') ) : ?>
			<?php $user = get_userdata( $post->post_author ) ?>
			<?php printf( si__('Added by %s'), $user->display_name )  ?>
		<?php else: ?>
			<?php si_e('Added by SI')  ?>
		<?php endif ?>
	</p></dd>
	
	<?php foreach ( $history as $item_id => $data ): ?>
		<dt class="record record-<?php echo $item_id ?>">
			<span class="history_deletion"><button data-id="<?php echo $item_id ?>" class="delete_record del_button">X</button></span>
			<span class="history_status <?php echo esc_attr( $data['status_type'] ); ?>"><?php echo esc_html( $data['type'] ) ?></span><br/>
			<span class="history_date"><?php echo date_i18n( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), strtotime( $data['post_date'] ) ) ?></span>
		</dt>

		<dd class="record record-<?php echo $item_id ?>">
			<?php if ( $data['status_type'] == SI_Notifications::RECORD ): ?>
				<p>
					<?php echo esc_html( $data['update_title'] ) ?>
					<br/><a href="#TB_inline?width=600&height=380&inlineId=notification_message_<?php echo (int) $item_id ?>" id="show_notification_tb_link_<?php echo (int) $item_id ?>" class="thickbox si_tooltip notification_message" title="<?php si_e('View Message') ?>"><?php si_e('View Message') ?></a>
				</p>
				<div id="notification_message_<?php echo (int) $item_id ?>" class="cloak">
					<?php echo wpautop( $data['content'] ) ?>
				</div>
			<?php elseif ( $data['status_type'] == SI_Importer::RECORD ): ?>
				<p>
					<?php echo esc_html( $data['update_title'] ) ?>
					<br/><a href="#TB_inline?width=600&height=380&inlineId=notification_message_<?php echo (int) $item_id ?>" id="show_notification_tb_link_<?php echo (int) $item_id ?>" class="thickbox si_tooltip notification_message" title="<?php si_e('View Data') ?>"><?php si_e('View Data') ?></a>
				</p>
				<div id="notification_message_<?php echo (int) $item_id ?>" class="cloak">
					<?php prp( json_decode( $data['content'] ) ); ?>
				</div>
			<?php elseif ( $data['status_type'] == SI_Invoices::VIEWED_STATUS_UPDATE ) : ?>
				<p>
					<?php echo esc_html( $data['update_title'] ) ?>
				</p>
			<?php else: ?>
				<?php echo wpautop( $data['content'] ) ?>
			<?php endif ?>
			
		</dd>
	<?php endforeach ?>
</dl>

<div id="private_note_wrap">
	<p>
		<textarea id="private_note" name="private_note" class="clearfix" disabled="disabled" style="height:40px;"></textarea>
		<?php if (  apply_filters( 'show_upgrade_messaging', true ) ) {
			printf( si__('<span class="helptip" title="Upgrade for Private Notes"></span>'), si_get_purchase_link() );
		} ?>
	</p>
</div>


<?php if ( !empty( $submission_fields ) ): ?>
	<div id="submission_fields_wrap">
		<h3><?php si_e('Form Submission') ?></h3>
		<dl>
			<?php foreach ( $submission_fields as $key => $value ): ?>
				<?php if ( isset( $value['data'] ) ): ?>
					<?php if ( $value['data']['label'] && $value['data']['type'] != 'hidden' ): ?>
						<dt><?php echo esc_html( $value['data']['label'] ) ?></dt>
						<?php if ( is_numeric( $value['value'] ) && strpos( $value['data']['label'], self::__('Type') ) !== false ): ?>
							<dd><p><?php 
									$term = get_term_by( 'id', $value['value'], SI_Estimate::PROJECT_TAXONOMY );
									if ( !is_wp_error( $term ) ) {
										self::_e( $term->name );
									}
								 ?></p></dd>
						<?php else: ?>
							<dd><?php echo wpautop( $value['value'] ) ?></dd>
						<?php endif ?>
					<?php endif ?>
				<?php endif ?>
			<?php endforeach ?>
		</dl>
	</div>

	<?php $media = get_attached_media( '' ); ?>
	<?php if ( !empty( $media ) ): ?>
		<p>
			<h3><?php si_e('Attachments') ?></h3>
			<ul>
				<?php foreach ( $media as $id => $mpost ): ?>
					<?php  $img = wp_get_attachment_image_src( $id, 'thumbnail', true ); ?>
					<li><a href="<?php echo wp_get_attachment_url( $id ) ?>" target="_blank" class="attachment_url"><img src="<?php echo esc_url( $img[0] ); ?>" alt="<?php esc_attr( $mpost->post_name ) ?>"></a></li>
				<?php endforeach ?>
			</ul>
		</p>
	<?php endif ?>

<?php endif ?>
