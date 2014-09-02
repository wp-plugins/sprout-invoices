<?php

/**
 * Clients Controller
 * 	
 *
 * @package Sprout_Invoice
 * @subpackage Clients
 */
class SI_Clients extends SI_Controller {
	const SUBMISSION_NONCE = 'si_client_submission';
	

	public static function init() {

		add_filter( 'si_submission_form_fields', array( __CLASS__, 'filter_estimate_submission_fields' ) );
		add_action( 'estimate_submitted',  array( __CLASS__, 'create_client_from_submission' ), 10, 2 );

		if ( is_admin() ) {

			// Help Sections
			add_action( 'admin_menu', array( get_class(), 'help_sections' ) );

			// Meta boxes
			add_action( 'admin_init', array( __CLASS__, 'register_meta_boxes' ) );
			add_action( 'do_meta_boxes', array( __CLASS__, 'modify_meta_boxes' ) );
			add_action( 'edit_form_top', array( __CLASS__, 'name_box' ) );

			// Admin columns
			add_filter( 'manage_edit-'.SI_Client::POST_TYPE.'_columns', array( __CLASS__, 'register_columns' ) );
			add_filter( 'manage_'.SI_Client::POST_TYPE.'_posts_custom_column', array( __CLASS__, 'column_display' ), 10, 2 );
			add_action( 'post_row_actions', array( __CLASS__, 'modify_row_actions' ), 10, 2 );


			// User Admin columns
			add_filter ( 'manage_users_columns', array( __CLASS__, 'user_register_columns' ) );
			add_filter ( 'manage_users_custom_column', array( __CLASS__, 'user_column_display' ), 10, 3 );

			// AJAX
			add_action( 'wp_ajax_sa_create_client',  array( __CLASS__, 'maybe_create_client' ), 10, 0 );

		}

		// Prevent Client role admin access
		add_action( 'admin_init', array( __CLASS__, 'redirect_clients' ) );

		// Admin bar
		add_filter( 'si_admin_bar', array( get_class(), 'add_link_to_admin_bar' ), 10, 1 );
	}

	/////////////////
	// Meta boxes //
	/////////////////

	/**
	 * Regsiter meta boxes for estimate editing.
	 *
	 * @return
	 */
	public static function register_meta_boxes() {
		// estimate specific
		$args = array(
			'si_client_information' => array(
				'title' => si__( 'Information' ),
				'show_callback' => array( __CLASS__, 'show_information_meta_box' ),
				'save_callback' => array( __CLASS__, 'save_meta_box_client_information' ),
				'context' => 'normal',
				'priority' => 'high',
				'save_priority' => 0
			),
			'si_client_submit' => array(
				'title' => 'Update',
				'show_callback' => array( __CLASS__, 'show_submit_meta_box' ),
				'save_callback' => array( __CLASS__, 'save_submit_meta_box' ),
				'context' => 'side',
				'priority' => 'high'
			),
			'si_client_history' => array(
				'title' => si__( 'History' ),
				'show_callback' => array( __CLASS__, 'show_client_history_view' ),
				'save_callback' => array( __CLASS__, '_save_null' ),
				'context' => 'normal',
				'priority' => 'low'
			)
		);
		do_action( 'sprout_meta_box', $args, SI_Client::POST_TYPE );
	}

	/**
	 * Remove publish box and add something custom for estimates
	 *
	 * @param string  $post_type
	 * @return
	 */
	public static function modify_meta_boxes( $post_type ) {
		if ( $post_type == SI_Client::POST_TYPE ) {
			remove_meta_box( 'submitdiv', null, 'side' );
		}
	}

	/**
	 * Add quick links
	 * @param  object $post
	 * @return
	 */
	public static function name_box( $post ) {
		if ( get_post_type( $post ) == SI_Client::POST_TYPE ) {
			$client = SI_Client::get_instance( $post->ID );
			self::load_view( 'admin/meta-boxes/clients/name', array(
					'client' => $client,
					'id' => $post->ID,
					'status' => $post->post_status
				) );
		}
	}

	/**
	 * Show custom submit box.
	 * @param  WP_Post $post         
	 * @param  array $metabox      
	 * @return                
	 */
	public static function show_submit_meta_box( $post, $metabox ) {
		$notification = SI_Notification::get_instance( $post->ID );
		$client = SI_Client::get_instance( $post->ID );

		$args = apply_filters( 'si_get_users_for_association_args', array( 'fields' => array( 'ID', 'user_email', 'display_name' ) ) );
		$users = get_users( $args );
		self::load_view( 'admin/meta-boxes/clients/submit', array(
				'id' => $post->ID,
				'client' => $client,
				'post' => $post,
				'associated_users' => $client->get_associated_users(),
				'users' => $users,
				'invoices' => $client->get_invoices(),
				'estimates' => $client->get_estimates(),
			), FALSE );
	}

	/**
	 * Information
	 * @param  object $post
	 * @return
	 */
	public static function show_information_meta_box( $post ) {
		if ( get_post_type( $post ) == SI_Client::POST_TYPE ) {
			$client = SI_Client::get_instance( $post->ID );
			self::load_view( 'admin/meta-boxes/clients/info', array(
					'client' => $client,
					'id' => $post->ID,
					'fields' => self::form_fields( FALSE ),
					'address' => $client->get_address(),
					'website' => $client->get_website()
				) );
		}
	}

	/**
	 * Saving info meta
	 * @param  int $post_id       
	 * @param  object $post          
	 * @param  array $callback_args 
	 * @param  int $estimate_id   
	 * @return                 
	 */
	public static function save_meta_box_client_information( $post_id, $post, $callback_args, $estimate_id = NULL ) {
		$name = ( isset( $_POST['sa_metabox_name'] ) && $_POST['sa_metabox_name'] != '' ) ? $_POST['sa_metabox_name'] : '' ;
		$website = ( isset( $_POST['sa_metabox_website'] ) && $_POST['sa_metabox_website'] != '' ) ? $_POST['sa_metabox_website'] : '' ;

		$address = array(
			'street' => isset( $_POST['sa_metabox_street'] ) ? $_POST['sa_metabox_street'] : '',
			'city' => isset( $_POST['sa_metabox_city'] ) ? $_POST['sa_metabox_city'] : '',
			'zone' => isset( $_POST['sa_metabox_zone'] ) ? $_POST['sa_metabox_zone'] : '',
			'postal_code' => isset( $_POST['sa_metabox_postal_code'] ) ? $_POST['sa_metabox_postal_code'] : '',
			'country' => isset( $_POST['sa_metabox_country'] ) ? $_POST['sa_metabox_country'] : '',
		);

		$client = SI_Client::get_instance( $post_id );
		$client->set_website( $website );
		$client->set_address( $address );

		if ( $name && $name != get_the_title( $post_id ) ) {
			$client_post = array(
				'ID' => $post_id,
				'post_title' => $name
				);

			// Update the post into the database
			wp_update_post( $client_post );
		}
	}

	/**
	 * Saving submit meta
	 * @param  int $post_id       
	 * @param  object $post          
	 * @param  array $callback_args 
	 * @param  int $estimate_id   
	 * @return                 
	 */
	public static function save_submit_meta_box( $post_id, $post, $callback_args, $estimate_id = NULL ) {
		if ( !isset( $_POST['associated_users'] ) )
			return;

		$client = SI_Client::get_instance( $post_id );
		$client->clear_associated_users();

		foreach ( $_POST['associated_users'] as $user_id ) {
			$client->add_associated_user($user_id);
		}

	}


	/**
	 * Show the history
	 *
	 * @param WP_Post $post
	 * @param array   $metabox
	 * @return
	 */
	public static function show_client_history_view( $post, $metabox ) {
		if ( $post->post_status == 'auto-draft' ) {
			printf( '<p>%s</p>', si__( 'No history available.' ) );
			return;
		}
		$client = SI_Client::get_instance( $post->ID );
		self::load_view( 'admin/meta-boxes/clients/history', array(
				'id' => $post->ID,
				'post' => $post,
				'estimate' => $client,
				'historical_records' => array_reverse( $client->get_history() ),
			), FALSE );
	}


	////////////
	// Misc. //
	////////////
	
	/**
	 * Redirect any clients away from the admin.
	 * @return  
	 */
	public static function redirect_clients() {
		$user = wp_get_current_user();
		if ( !isset( $user->roles ) || $user->roles[0] == 'sa_client' ) {
			wp_redirect( home_url() );
			exit();
		}

	}


	////////////////////
	// Admin Columns //
	////////////////////

	/**
	 * Overload the columns for the invoice post type admin
	 *
	 * @param array   $columns
	 * @return array
	 */
	public static function register_columns( $columns ) {
		// Remove all default columns
		unset( $columns['date'] );
		unset( $columns['title'] );
		unset( $columns['comments'] );
		unset( $columns['author'] );
		$columns['title'] = self::__( 'Client' );
		$columns['info'] = self::__( 'Info' );
		$columns['invoices'] = self::__( 'Invoices' );
		$columns['estimates'] = self::__( 'Estimates' );
		return $columns;
	}

	/**
	 * Display the content for the column
	 *
	 * @param string  $column_name
	 * @param int     $id          post_id
	 * @return string
	 */
	public static function column_display( $column_name, $id ) {
		$client = SI_Client::get_instance( $id );

		if ( !is_a( $client, 'SI_Client' ) )
			return; // return for that temp post

		switch ( $column_name ) {
		
		case 'info':
			
			echo '<p>';
			$address = si_format_address( $client->get_address(), 'string', '<br/>' );
			echo $address;
			if ( $address != '' ) {
				echo '<br/>';
			}
			echo make_clickable( esc_url( $client->get_website() ) );
			echo '</p>';

			$associated_users = $client->get_associated_users();
			echo '<p>';
			printf( '<b>%s</b>: ', si__('Users') );
			if ( !empty( $associated_users ) ) {
				$users_print = array();
				foreach ($associated_users as $user_id) {
					$user = get_userdata( $user_id );
					$users_print[] = sprintf( '<span class="associated_user"><a href="%s">%s</a></span>', get_edit_user_link( $user_id ), $user->display_name );
				}
				echo implode( ', ', $users_print );
			}
			else {
				echo si__('No associated users');
			}
			echo '</p>';
			
			break;

		case 'invoices':

			$invoices = $client->get_invoices();
			$split = 3;
			$split_invoices = array_slice( $invoices, 0, $split );
			if ( !empty( $split_invoices ) ) {
				echo "<dl>";
				foreach ($split_invoices as $invoice_id) {
					printf( '<dt>%s</dt><dd><a href="%s">%s</a></dd>', get_post_time( get_option('date_format'), false, $invoice_id ), get_edit_post_link( $invoice_id ), get_the_title( $invoice_id ) );
				}
				echo "</dl>";
				if ( count( $invoices ) > $split ) {
					printf( '<span class="description">' . si__('...%s of <a href="%s">%s</a> most recent shown') . '</span>', $split, get_edit_post_link( $id ), count( $invoices ) );
				}
			}
			else {
				printf( '<em>%s</em>', si__('No invoices') );
			}
			break;

		case 'estimates':

			$estimates = $client->get_estimates();
			$split = 3;
			$split_estimates = array_slice( $estimates, 0, $split );
			if ( !empty( $split_estimates ) ) {
				echo "<dl>";
				foreach ($split_estimates as $estimate_id) {
					printf( '<dt>%s</dt><dd><a href="%s">%s</a></dd>', get_post_time( get_option('date_format'), false, $estimate_id ), get_edit_post_link( $estimate_id ), get_the_title( $estimate_id ) );
				}
				echo "</dl>";
				if ( count( $estimates ) > $split ) {
					printf( '<span class="description">' . si__('...%s of <a href="%s">%s</a> most recent shown') . '</span>', $split, get_edit_post_link( $id ), count( $estimates ) );
				}
			}
			else {
				printf( '<em>%s</em>', si__('No estimates') );
			}
			break;

		default:
			// code...
			break;
		}

	}

	/**
	 * Register the client column. In CSS make it small.
	 * @param  array $columns 
	 * @return array          
	 */
	public static function user_register_columns( $columns ) {
		$columns['client'] = '<div class="dashicons dashicons-id-alt"></div>';
		return $columns;
	}

	/**
	 * User column display
	 * @param  string $empty       
	 * @param  string $column_name 
	 * @param  int $id          
	 * @return string              
	 */
	public static function user_column_display( $empty = '', $column_name, $id ) {
		switch ( $column_name ) {
			case 'client':
				$client_ids = SI_Client::get_clients_by_user( $id );
				
				if ( !empty( $client_ids ) ) {
					foreach ( $client_ids as $client_id ) {
						$string .= sprintf( self::__( '<a class="doc_link" title="%s" href="%s">%s</a>' ), get_the_title( $client_id ), get_edit_post_link( $client_id ), '<div class="dashicons dashicons-id-alt"></div>' );
					}
					return $string;
				}
				break;

			default:
				break;
		}
	}

	/**
	 * Filter the array of row action links below the title.
	 *
	 * @param array   $actions An array of row action links.
	 * @param WP_Post $post    The post object.
	 */
	public static function modify_row_actions( $actions = array(), $post = array() ) {
		if ( $post->post_type == SI_Client::POST_TYPE ) {
			unset( $actions['trash'] );
			// remove quick edit
			unset( $actions['inline hide-if-no-js'] );
		}
		return $actions;
	}


	////////////
	// Forms //
	////////////

	public static function form_fields( $required = TRUE ) {

		$fields['name'] = array(
			'weight' => 1,
			'label' => self::__( 'Company Name' ),
			'type' => 'text',
			'required' => $required,
			'default' => ''
		);

		$fields['email'] = array(
			'weight' => 3,
			'label' => self::__( 'Email' ),
			'type' => 'text',
			'required' => $required,
			'default' => ''
		);

		$fields['website'] = array(
			'weight' => 120,
			'label' => self::__( 'Website' ),
			'type' => 'text',
			'required' => $required,
			'placeholder' => 'http://'
		);

		$fields['nonce'] = array(
			'type' => 'hidden',
			'value' => wp_create_nonce( self::SUBMISSION_NONCE ),
			'weight' => 10000
		);

		$fields = array_merge( $fields, self::get_standard_address_fields( $required ) );

		$fields = apply_filters( 'si_client_form_fields', $fields );
		uasort( $fields, array( __CLASS__, 'sort_by_weight' ) );
		return $fields;
	}

	/**
	 * Maybe create a user if one is not already created.
	 * @param  array  $args 
	 * @return $user_id       
	 */
	public static function create_user( $args = array() ) {
		$defaults = array(
			'user_login' => '',
			'user_name' => '',
			'password' => '',
			'user_email' => '',
			'first_name' => '',
			'last_name' => '',
			'user_url' => '',
			'role' => SI_Client::USER_ROLE
		);
		$parsed_args = wp_parse_args( $args, $defaults );
		extract( $parsed_args );

		// check if the user already exists.
		if ( $user = get_user_by('email', $user_email ) ) {
			return $user->ID;
		}

		$user_id = wp_insert_user( $parsed_args );
		return $user_id;
	}

	//////////////////
	// Submissions //
	//////////////////

	/**
	 * Filter the submission fields if the user is logged in and a client is already created.
	 * @param  array  $fields 
	 * @return array         
	 */
	public static function filter_estimate_submission_fields( $fields = array() ) {
		if ( is_user_logged_in() ) {
			$client_ids = SI_Client::get_clients_by_user( get_current_user_id() );
			if ( !empty( $client_ids ) ) {
				$client_id = array_pop( $client_ids );
			}
			if ( get_post_type( $client_id ) == SI_Client::POST_TYPE ) {
				// If the client exists don't show the client fields
				unset( $fields['name'] );
				unset( $fields['client_name'] );
				unset( $fields['email'] );
				unset( $fields['website'] );
				$fields['client_id'] = array(
					'type' => 'hidden',
					'value' => $client_id
				);
			}
		}
		return $fields;
	}


	/**
	 * Hooked into the estimate submission form. Create a client
	 * if one already doesn't exist.
	 * @param  SI_Estimate $estimate    
	 * @param  array       $parsed_args 
	 * @return                    
	 */
	public static function create_client_from_submission( SI_Estimate $estimate, $parsed_args = array() ) {	
		$client_id = ( isset( $_REQUEST['client_id'] ) && get_post_type( $_REQUEST['client_id'] ) == SI_Client::POST_TYPE ) ? $_REQUEST['client_id'] : 0;
		$user_id = get_current_user_id();

		// check to see if the user exists by email
		if ( isset( $_REQUEST['sa_estimate_email'] ) && $_REQUEST['sa_estimate_email'] != '' ) {
			if ( $user = get_user_by('email', $_REQUEST['sa_estimate_email'] ) ) {
				$user_id = $user->ID;
			}
		}

		// Check to see if the user is assigned to a client already
		if ( !$client_id ) {
			$client_ids = SI_Client::get_clients_by_user( $user_id );
			if ( !empty( $client_ids ) ) {
				$client_id = array_pop( $client_ids );
			}
		}
		
		// Create a user for the submission if an email is provided.
		if ( !$user_id ) {
			// email is critical
			if ( isset( $_REQUEST['sa_estimate_email'] ) && $_REQUEST['sa_estimate_email'] != '' ) {
				$user_args = array(
					'user_login' => self::esc__($_REQUEST['sa_estimate_email']),
					'display_name' => isset( $_REQUEST['sa_estimate_client_name'] ) ? self::esc__($_REQUEST['sa_estimate_client_name']) : self::esc__($_REQUEST['sa_estimate_email']),
					'user_pass' => wp_generate_password(), // random password
					'user_email' => isset( $_REQUEST['sa_estimate_email'] ) ? self::esc__($_REQUEST['sa_estimate_email']) : '',
					'first_name' => si_split_full_name( self::esc__($_REQUEST['sa_estimate_name']), 'first' ),
					'last_name' => si_split_full_name( self::esc__($_REQUEST['sa_estimate_name']), 'last' ),
					'user_url' => isset( $_REQUEST['sa_estimate_website'] ) ? self::esc__($_REQUEST['sa_estimate_website']) : ''
				);
				$user_id = self::create_user( $user_args );
			}
			
		}

		// create the client based on what's submitted.
		if ( !$client_id ) {
			$address = array(
				'street' => isset( $_REQUEST['sa_contact_street'] ) ?self::esc__( $_REQUEST['sa_contact_street']) : '',
				'city' => isset( $_REQUEST['sa_contact_city'] ) ? self::esc__($_REQUEST['sa_contact_city']) : '',
				'zone' => isset( $_REQUEST['sa_contact_zone'] ) ? self::esc__($_REQUEST['sa_contact_zone']) : '',
				'postal_code' => isset( $_REQUEST['sa_contact_postal_code'] ) ? self::esc__($_REQUEST['sa_contact_postal_code']) : '',
				'country' => isset( $_REQUEST['sa_contact_country'] ) ? self::esc__($_REQUEST['sa_contact_country']) : '',
			);

			$args = array(
				'company_name' => isset( $_REQUEST['sa_estimate_client_name'] ) ? self::esc__($_REQUEST['sa_estimate_client_name']) : '',
				'website' => isset( $_REQUEST['sa_estimate_website'] ) ? self::esc__($_REQUEST['sa_estimate_website']) : '',
				'address' => $address,
				'user_id' => $user_id
			);

			$client_id = SI_Client::new_client( $args );
		}
		
		// Set the estimates client
		$estimate->set_client_id( $client_id );

	}

	/**
	 * AJAX submission from admin.
	 * @return json response
	 */
	public static function maybe_create_client() {
		// form maybe be serialized
		if ( isset( $_REQUEST['serialized_fields'] ) ) {
			foreach ( $_REQUEST['serialized_fields'] as $key => $data ) {
				$_REQUEST[$data['name']] = $data['value'];
			}
		}

		if ( !isset( $_REQUEST['sa_client_nonce'] ) )
			self::ajax_fail( 'Forget something?' );

		$nonce = $_REQUEST['sa_client_nonce'];
		if ( !wp_verify_nonce( $nonce, self::SUBMISSION_NONCE ) )
			self::ajax_fail( 'Not going to fall for it!' );

		if ( !current_user_can( 'create_posts' ) )
			return;
		
		$user_id = 0;
		// Attempt to create a user
		if ( isset( $_REQUEST['sa_client_email'] ) && $_REQUEST['sa_client_email'] != '' ) {
			$user_args = array(
				'user_login' => self::esc__($_REQUEST['sa_client_email']),
				'display_name' => isset( $_REQUEST['sa_client_name'] ) ? self::esc__($_REQUEST['sa_client_name']) : self::esc__($_REQUEST['sa_client_email']),
				'user_pass' => wp_generate_password(), // random password
				'user_email' => isset( $_REQUEST['sa_client_email'] ) ? self::esc__($_REQUEST['sa_client_email']) : '',
				'first_name' => isset( $_REQUEST['sa_client_first_name'] ) ? self::esc__($_REQUEST['sa_client_first_name']) : '',
				'last_name' => isset( $_REQUEST['sa_client_last_name'] ) ? self::esc__($_REQUEST['sa_client_last_name']) : '',
				'user_url' => isset( $_REQUEST['sa_client_website'] ) ? self::esc__($_REQUEST['sa_client_website']) : ''
			);
			$user_id = self::create_user( $user_args );
		}

		// Create the client
		$address = array(
			'street' => isset( $_REQUEST['sa_client_street'] ) ? self::esc__($_REQUEST['sa_client_street']) : '',
			'city' => isset( $_REQUEST['sa_client_city'] ) ? self::esc__($_REQUEST['sa_client_city']) : '',
			'zone' => isset( $_REQUEST['sa_client_zone'] ) ? self::esc__($_REQUEST['sa_client_zone']) : '',
			'postal_code' => isset( $_REQUEST['sa_client_postal_code'] ) ? self::esc__($_REQUEST['sa_client_postal_code']) : '',
			'country' => isset( $_REQUEST['sa_client_country'] ) ? self::esc__($_REQUEST['sa_client_country']) : '',
		);
		$args = array(
			'company_name' => isset( $_REQUEST['sa_client_name'] ) ? self::esc__($_REQUEST['sa_client_name']) : '',
			'website' => isset( $_REQUEST['sa_client_website'] ) ? self::esc__($_REQUEST['sa_client_website']) : '',
			'address' => $address,
			'user_id' => $user_id
		);
		$client_id = SI_Client::new_client( $args );

		$response = array(
				'id' => $client_id,
				'title' => get_the_title( $client_id ),
			);

		header( 'Content-type: application/json' );
		if ( SA_DEV ) header( 'Access-Control-Allow-Origin: *' );
		echo json_encode( $response );
		exit();
	}

	//////////////
	// Utility //
	//////////////


	public static function add_link_to_admin_bar( $items ) {
		$items[] = array(
			'id' => 'edit_clients',
			'title' => self::__( 'Clients' ),
			'href' => admin_url( 'edit.php?post_type='.SI_Client::POST_TYPE ),
			'weight' => 0,
		);
		return $items;
	}

	////////////////
	// Admin Help //
	////////////////

	public static function help_sections() {
		add_action( 'load-edit.php', array( __CLASS__, 'help_tabs' ) );
		add_action( 'load-post.php', array( __CLASS__, 'help_tabs' ) );
		add_action( 'load-post-new.php', array( get_class(), 'help_tabs' ) );
	}

	public static function help_tabs() {
		$post_type = '';
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == SI_Client::POST_TYPE ) {
			$post_type = SI_Client::POST_TYPE;
		}
		if ( $post_type == '' && isset( $_GET['post'] ) ) {
			$post_type = get_post_type( $_GET['post'] );
		}
		if ( $post_type == SI_Client::POST_TYPE ) {
			// get screen and add sections.
			$screen = get_current_screen();

			$screen->add_help_tab( array(
					'id' => 'edit-clients',
					'title' => self::__( 'Manage Clients' ),
					'content' => sprintf( '<p>%s</p><p>%s</p>', self::__('The information here is used for estimates and invoices and includes settings to: Edit Company Name, Edit the company address, and Edit their website url.'), self::__('<b>Important note:</b> when clients are created new WordPress users are also created and given the “client” role. Creating users will allow for future functionality, i.e. client dashboards.') ),
				) );

			$screen->add_help_tab( array(
					'id' => 'associate-users',
					'title' => self::__( 'Associated Users' ),
					'content' => sprintf( '<p>%s</p>', self::__('When clients are created a WP user is created and associated and clients are not limited to a single user. Not limited a client to a single user allows for you to have multiple points of contact at/for a company/client. Example, the recipients for sending estimate and invoice notifications are these associated users.') ),
				) );

			$screen->add_help_tab( array(
					'id' => 'client-history',
					'title' => self::__( 'Client History' ),
					'content' => sprintf( '<p>%s</p>', self::__('Important points are shown in the client history and just like estimate and invoices private notes can be added for only you and other team members to see.') ),
				) );

			$screen->add_help_tab( array(
					'id' => 'client-invoices',
					'title' => self::__( 'Invoices and Estimates' ),
					'content' => sprintf( '<p>%s</p>', self::__('All invoices and estimates associated with the client are shown below the associated users option. This provides a quick way to jump to the record you need to see.') ),
				) );

			$screen->set_help_sidebar(
				sprintf( '<p><strong>%s</strong></p>', self::__('For more information:') ) .
				sprintf( '<p><a href="%s" class="button">%s</a></p>', 'https://sproutapps.co/support/knowledgebase/sprout-invoices/clients/', self::__('Documentation') ) .
				sprintf( '<p><a href="%s" class="button">%s</a></p>', 'https://sproutapps.co/support/', self::__('Support') )
			);
		}
	}
}
