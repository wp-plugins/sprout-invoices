<?php

/**
 * DO NOT EDIT THIS FILE! Instead customize it via a theme override.
 *
 * Any edit will not be saved when this plugin is upgraded. Not upgrading will prevent you from receiving new features,
 * limit our ability to support your site and potentially expose your site to security risk that an upgrade has fixed.
 *
 * Theme overrides are easy too, so there's no excuse...
 *
 * https://sproutapps.co/support/knowledgebase/sprout-invoices/customizing-templates/
 *
 * You find something that you're not able to customize? We want your experience to be awesome so let support know and we'll be able to help you.
 *
 */

do_action( 'pre_si_estimate_view' ); ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php wp_title( '|', true, 'right' ); ?></title>
		<link rel="profile" href="http://gmpg.org/xfn/11" />
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
		<?php si_head(); // styles and scripts are filtered ?>
		<meta name="robots" content="noindex, nofollow" />
	</head>

	<body id="estimate" <?php body_class(); ?>>
		
		<div id="outer_doc_wrap">

			<div id="doc_header_wrap" class="sticky_header">
				<header id="header_title">
					<span class="header_id"><?php printf( si__('Estimate %s'), si_get_estimate_id() ) ?></span>
					<div id="doc_actions">
						<?php do_action( 'si_doc_actions_pre' ) ?>
						<?php if ( !si_is_estimate_approved() ): ?>
							<a href="#accept" class="button primary_button status_change" data-status-change="accept" data-id="<?php the_ID() ?>" data-nonce="<?php echo wp_create_nonce( SI_Controller::NONCE ) ?>"><?php si_e('Accept Estimate') ?></a>
						<?php else: ?>
							<a href="javascript:void(0)" class="button primary_button disabled"><?php si_e('Accepted') ?></a>
						<?php endif ?>
						<?php if ( !si_is_estimate_declined() ): ?>
							<a href="#decline" class="button status_change" data-status-change="decline" data-id="<?php the_ID() ?>" data-nonce="<?php echo wp_create_nonce( SI_Controller::NONCE ) ?>"><?php si_e('Decline Estimate') ?></a>
						<?php else: ?>
							<a href="javascript:void(0)" class="button disabled"><?php si_e('Declined') ?></a>
						<?php endif ?>
						<?php do_action( 'si_doc_actions' ) ?>
					</div><!-- #doc_actions -->
				</header><!-- #header_title -->
			</div><!-- #doc_header_wrap -->

			<div id="document_wrap">

				<div id="doc">

					<section id="header_wrap" class="clearfix">

						<div id="header_logo" class="clearfix">
							
							<header role="banner">
								<div class="header_info">
									<h2 class="doc_type"><?php si_e('Estimate') ?></h2>
									<p class="title"><?php the_title() ?></p>
								</div>

								<h1 id="logo">
									<?php if ( get_theme_mod( 'si_logo' ) ) : ?>
										<img src="<?php echo esc_url( get_theme_mod( 'si_logo', si_doc_header_logo_url() ) ); ?>" alt="document logo" >
									<?php else: ?>
										<img src="<?php echo si_doc_header_logo_url() ?>" alt="document logo" >
									<?php endif; ?>
								</h1>
							</header><!-- /header -->

						</div><!-- #header_logo -->

						<div id="vcards">
							<?php do_action( 'si_document_vcards_pre' ) ?>
							<dl id="doc_address_info">
								<dl class="from_addy">
									<dt>
										<span class="dt_heading"><?php si_e('From') ?></span>
									</dt>
									<dd>
										<b><?php si_company_name() ?></b> 
										<?php si_doc_address() ?>
									</dd>
								</dl>
								<?php if ( si_get_estimate_client_id() ): ?>
									<dl class="client_addy">
										<dt>
											<span class="dt_heading"><?php si_e('To') ?></span>
										</dt>
										<dd>
											<b><?php echo get_the_title( si_get_estimate_client_id() ) ?>
											
											<?php do_action( 'si_document_client_addy' ) ?></b> 
											<?php si_client_address( si_get_estimate_client_id() ) ?>

										</dd>
									</dl>
								<?php endif ?>
								<?php do_action( 'si_document_vcards' ) ?>
							</dl><!-- #doc_address_info -->
						</div><!-- #vcards -->
						
						<div class="doc_details clearfix">
							<?php do_action( 'si_document_details_pre' ) ?>

							<dl class="date">
								<dt><span class="dt_heading"><?php si_e('Date') ?></span></dt>
								<dd><?php si_estimate_issue_date() ?></dd>
							</dl>

							<?php if ( si_get_estimate_id() ): ?>
								<dl class="estimate_number">
									<dt><span class="dt_heading"><?php si_e('Estimate Number') ?></span></dt>
									<dd><?php si_estimate_id() ?></dd>
								</dl>
							<?php endif ?>

							<?php if ( si_get_estimate_po_number() ): ?>
								<dl class="estimate_po_number">
									<dt><span class="dt_heading"><?php si_e('PO Number') ?></span></dt>
									<dd><?php si_estimate_po_number() ?></dd>
								</dl>
							<?php endif ?>

							<?php if ( si_get_estimate_expiration_date() ): ?>
								<dl class="date">
									<dt><span class="dt_heading"><?php si_e('Expiration Date') ?></span></dt>
									<dd><?php si_estimate_expiration_date() ?></dd>
								</dl>
							<?php endif ?>

							<?php do_action( 'si_document_details_totals' ) ?>

							<dl class="doc_total">
								<dt><span class="dt_heading"><?php si_e('Estimate Total') ?></span></dt>
								<dd><?php sa_formatted_money( si_get_estimate_total() ) ?></dd>
							</dl>

							<?php do_action( 'si_document_details' ) ?>
						</div><!-- #doc_details -->

					</section>

					<?php
						$line_items = si_get_estimate_line_items();
						$has_percentage_adj = FALSE;
						foreach ( $line_items as $position => $data ) {
							if ( isset( $data['tax'] ) && $data['tax'] ) {
								$has_percentage_adj = TRUE;
							}
						} ?>

					<section id="doc_line_items_wrap" class="clearfix">
						<div id="doc_line_items" class="clearfix">
							<div id="line_items_header">
								<?php do_action( 'si_document_line_items_header' ) ?>
								<div class="line_item">
									<?php echo si_line_item_header_front_end( 'estimates', $has_percentage_adj ) ?>
								</div>
							</div>
							<ol id="items">
								<?php do_action( 'si_document_line_items' ) ?>
								<?php foreach ( $line_items as $position => $data ): ?>
									<?php if ( is_int( $position ) ): // is not a child ?>
										<li class="item" data-id="<?php echo $position ?>">
											<?php
												// get the children of this top level item
												$children = si_line_item_get_children( $position, $line_items ); ?>

											<?php 
												// build single item
												echo si_line_item_build( $position, $line_items, $children ) ?>

											<?php if ( !empty( $children ) ): // if has children, loop and show  ?>
												<ol class="items_list">
													<?php foreach ( $children as $child_position ): ?>
														<li class="item" data-id="<?php echo $child_position ?>"><?php echo si_line_item_build( $child_position, $line_items ) ?></li>
													<?php endforeach ?>
												</ol>
											<?php endif ?>
										</li>
									<?php endif ?>
								<?php endforeach ?>

							</ol>

							<footer id="line_items_footer" class="clearfix">
								<?php do_action( 'si_document_line_items_footer' ) ?>
								<div id="line_items_totals">
									<div id="line_subtotal">
										<b><?php si_e('Subtotal') ?></b>
										<?php sa_formatted_money( si_get_estimate_subtotal() ) ?>
									</div>

									<?php if ( si_get_estimate_taxes_total() ): ?>
										<div id="line_taxes">
											<b><?php si_e('Taxes') ?></b>
											<?php sa_formatted_money( si_get_estimate_taxes_total() ) ?>
										</div>
									<?php endif ?>

									<div id="line_total">
										<b title="<?php si_e('Total includes discounts and other fees.') ?>" class="helptip"><?php si_e('Total') ?></b>
										<?php sa_formatted_money( si_get_estimate_total() ) ?>
									</div>
								</div>
							</footer>
						</div><!-- #doc_line_items -->

					</section>

					<section id="doc_notes">
						<?php do_action( 'si_document_notes_pre' ) ?>
						<?php if ( strlen( si_get_invoice_notes() ) > 1 ): ?>

						<?php do_action( 'si_document_notes' ) ?>
						<div id="doc_notes">
							<h2><?php si_e('Notes') ?></h2>
							<?php si_estimate_notes() ?>
						</div><!-- #doc_notes -->
						
						<?php endif ?>

						<?php if ( strlen( si_get_invoice_terms() ) > 1 ): ?>
						
						<?php do_action( 'si_document_terms' ) ?>
						<div id="doc_terms">
							<h2><?php si_e('Terms') ?></h2>
							<?php si_estimate_terms() ?>
						</div><!-- #doc_terms -->
						
						<?php endif ?>

					</section>
				
				</div><!-- #doc -->

				<div id="footer_wrap">
					<?php do_action( 'si_document_footer' ) ?>
					<aside>
						<ul class="doc_footer_items">
							<li class="doc_footer_item">
								<?php printf( '<strong>%s</strong> %s', '<div class="dashicons dashicons-admin-site"></div>', make_clickable( home_url() ) ) ?>
							</li>
							<?php if ( si_get_company_email() ): ?>
								<li class="doc_footer_item">
									<?php printf( '<strong>%s</strong> %s', '<div class="dashicons dashicons-email-alt"></div>', make_clickable( si_get_company_email() ) ) ?>
								</li>
							<?php endif ?>
						</ul>
					</aside>
				</div><!-- #footer_wrap -->

		</div><!-- #outer_doc_wrap -->
		
		<div id="doc_history">
			<?php do_action( 'si_document_history' ) ?>
			<?php foreach ( si_doc_history_records() as $item_id => $data ): ?>
				<dt>
					<span class="history_status <?php echo $data['status_type'] ?>"><?php echo $data['type']; ?></span><br/>
					<span class="history_date"><?php echo date( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), strtotime( $data['post_date'] ) ) ?></span>
				</dt>

				<dd>
					<?php if ( $data['status_type'] == SI_Notifications::RECORD ): ?>
						<p>
							<?php echo $update_title ?>
							<br/><a href="#TB_inline?width=600&height=380&inlineId=notification_message_<?php echo $item_id ?>" id="show_notification_tb_link_<?php echo $item_id ?>" class="thickbox si_tooltip notification_message" title="<?php si_e('View Message') ?>"><?php si_e('View Message') ?></a>
						</p>
						<div id="notification_message_<?php echo $item_id ?>" class="cloak">
							<?php echo wpautop( $data['content'] ) ?>
						</div>
					<?php elseif ( $data['status_type'] == SI_Invoices::VIEWED_STATUS_UPDATE ) : ?>
						<p>
							<?php echo $data['update_title'] ?>
						</p>
					<?php else: ?>
						<?php echo wpautop( $data['content'] ) ?>
					<?php endif ?>
					
				</dd>
			<?php endforeach ?>
		</div><!-- #doc_history -->

		<div id="footer_credit">
			<?php do_action( 'si_document_footer_credit' ) ?>
			<!--<p><?php si_e('Powered by Sprout Invoices') ?></p>-->
		</div><!-- #footer_messaging -->

	</body>
	<?php do_action( 'si_document_footer' ) ?>
	<?php si_footer() ?>
</html>
<?php do_action( 'estimate_viewed' ) ?>