<?php
/**
 * Meta boxes.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the service settings box.
 */
function gif_add_meta_boxes() {
	add_meta_box(
		'gif_service_settings',
		__( 'Service card', 'getitframed' ),
		'gif_render_service_box',
		'gif_service',
		'side',
		'default'
	);

	add_meta_box(
		'gif_enquiry_detail',
		__( 'Enquiry', 'getitframed' ),
		'gif_render_enquiry_box',
		'gif_enquiry',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'gif_add_meta_boxes' );

/**
 * Service card settings.
 *
 * @param WP_Post $post Current post.
 */
function gif_render_service_box( $post ) {
	wp_nonce_field( 'gif_service_save', 'gif_service_nonce' );

	$colour  = get_post_meta( $post->ID, '_gif_card_colour', true );
	$strap   = get_post_meta( $post->ID, '_gif_strapline', true );
	$choices = gif_card_colour_choices();
	?>
	<p>
		<label for="gif_card_colour"><strong><?php esc_html_e( 'Card colour', 'getitframed' ); ?></strong></label><br>
		<select name="gif_card_colour" id="gif_card_colour" style="width:100%">
			<option value=""><?php esc_html_e( '— Choose automatically —', 'getitframed' ); ?></option>
			<?php foreach ( $choices as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $colour, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="gif_strapline"><strong><?php esc_html_e( 'Strapline', 'getitframed' ); ?></strong></label><br>
		<input type="text" name="gif_strapline" id="gif_strapline" style="width:100%"
			value="<?php echo esc_attr( $strap ); ?>">
		<span class="description"><?php esc_html_e( 'Optional line under the heading on the service page.', 'getitframed' ); ?></span>
	</p>
	<p class="description">
		<?php esc_html_e( 'The short description shown on the card comes from the Excerpt field. The card image is the Featured Image.', 'getitframed' ); ?>
	</p>
	<?php
}

/**
 * Save the service box.
 *
 * @param int $post_id Post ID.
 */
function gif_save_service_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['gif_service_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['gif_service_nonce'] ) ), 'gif_service_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$colour  = isset( $_POST['gif_card_colour'] ) ? sanitize_key( wp_unslash( $_POST['gif_card_colour'] ) ) : '';
	$allowed = array_keys( gif_card_colour_choices() );
	if ( $colour && in_array( $colour, $allowed, true ) ) {
		update_post_meta( $post_id, '_gif_card_colour', $colour );
	} else {
		delete_post_meta( $post_id, '_gif_card_colour' );
	}

	$strap = isset( $_POST['gif_strapline'] ) ? sanitize_text_field( wp_unslash( $_POST['gif_strapline'] ) ) : '';
	if ( $strap ) {
		update_post_meta( $post_id, '_gif_strapline', $strap );
	} else {
		delete_post_meta( $post_id, '_gif_strapline' );
	}
}
add_action( 'save_post_gif_service', 'gif_save_service_meta' );

/**
 * Read-only view of a stored enquiry.
 *
 * @param WP_Post $post Current post.
 */
function gif_render_enquiry_box( $post ) {
	$fields = array(
		'name'    => __( 'Name', 'getitframed' ),
		'email'   => __( 'Email', 'getitframed' ),
		'phone'   => __( 'Phone', 'getitframed' ),
		'service' => __( 'Service', 'getitframed' ),
		'message' => __( 'Message', 'getitframed' ),
		'sent'    => __( 'Email delivered', 'getitframed' ),
		'ip'      => __( 'Submitted from', 'getitframed' ),
	);

	echo '<table class="widefat striped"><tbody>';
	foreach ( $fields as $key => $label ) {
		$value = get_post_meta( $post->ID, '_gif_' . $key, true );
		if ( 'sent' === $key ) {
			$value = $value ? __( 'Yes', 'getitframed' ) : __( 'No — this one only exists here', 'getitframed' );
		}
		if ( '' === $value ) {
			continue;
		}
		printf(
			'<tr><th style="width:150px">%s</th><td>%s</td></tr>',
			esc_html( $label ),
			nl2br( esc_html( $value ) )
		);
	}
	echo '</tbody></table>';
}

/**
 * Enquiry list columns.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function gif_enquiry_columns( $columns ) {
	return array(
		'cb'          => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'       => __( 'From', 'getitframed' ),
		'gif_email'   => __( 'Email', 'getitframed' ),
		'gif_service' => __( 'Service', 'getitframed' ),
		'gif_sent'    => __( 'Emailed', 'getitframed' ),
		'date'        => __( 'Received', 'getitframed' ),
	);
}
add_filter( 'manage_gif_enquiry_posts_columns', 'gif_enquiry_columns' );

/**
 * Enquiry list column content.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function gif_enquiry_column_content( $column, $post_id ) {
	if ( 'gif_email' === $column ) {
		$email = get_post_meta( $post_id, '_gif_email', true );
		if ( $email ) {
			printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $email ) );
		}
	}
	if ( 'gif_service' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_gif_service', true ) );
	}
	if ( 'gif_sent' === $column ) {
		echo get_post_meta( $post_id, '_gif_sent', true )
			? esc_html__( 'Yes', 'getitframed' )
			: '<span style="color:#b32d2e">' . esc_html__( 'Failed', 'getitframed' ) . '</span>';
	}
}
add_action( 'manage_gif_enquiry_posts_custom_column', 'gif_enquiry_column_content', 10, 2 );
