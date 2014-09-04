<?php 
	$fields['name']['default'] = get_the_title( $id );
	unset( $fields['email'] );
	unset( $fields['first_name'] );
	unset( $fields['last_name'] );
	$fields['street']['default']= ( isset( $address['street'] ) ) ? $address['street'] : '' ;
	$fields['city']['default']= ( isset( $address['city'] ) ) ? $address['city'] : '' ;
	$fields['zone']['default']= ( isset( $address['zone'] ) ) ? $address['zone'] : '' ;
	$fields['postal_code']['default']= ( isset( $address['postal_code'] ) ) ? $address['postal_code'] : '' ;
	$fields['country']['default']= ( isset( $address['country'] ) ) ? $address['country'] : '' ;
	$fields['website']['default'] = $website; ?>


<div id="client_fields" class="admin_fields clearfix">
	<?php sa_admin_fields( $fields ); ?>
</div>