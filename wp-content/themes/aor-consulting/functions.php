<?php
/**
 * Theme setup and contact form.
 *
 * @package AOR_Consulting
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aor_consulting_setup() {
	load_theme_textdomain( 'aor-consulting', get_template_directory() . '/languages' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'aor_consulting_setup' );

function aor_consulting_enqueue_styles() {
	wp_enqueue_style( 'aor-consulting', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_script( 'aor-consulting', get_theme_file_uri( 'assets/js/site.js' ), array(), wp_get_theme()->get( 'Version' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
}
add_action( 'wp_enqueue_scripts', 'aor_consulting_enqueue_styles' );

function aor_consulting_register_patterns() {
	register_block_pattern_category( 'aor-consulting', array( 'label' => __( 'AO-Risk Consulting', 'aor-consulting' ) ) );
}
add_action( 'init', 'aor_consulting_register_patterns' );

/** Only these subjects can enter an outgoing message. */
function aor_consulting_contact_subjects() {
	return array(
		'diagnostic'      => __( 'Diagnostic & cartographie des risques', 'aor-consulting' ),
		'gouvernance'     => __( 'Gouvernance & pilotage', 'aor-consulting' ),
		'resilience'      => __( 'Gestion de crise & continuité', 'aor-consulting' ),
		'specialisation'  => __( 'Risques fournisseurs & ESG', 'aor-consulting' ),
		'accompagnement'  => __( 'External Risk Officer & accompagnement', 'aor-consulting' ),
		'formation'       => __( 'Formation au contrôle interne', 'aor-consulting' ),
		'autre'           => __( 'Un autre sujet à explorer', 'aor-consulting' ),
	);
}

function aor_consulting_wants_json() {
	return isset( $_POST['aor_response'] ) && 'json' === $_POST['aor_response'];
}

/** Keep the same validation and status codes with and without JavaScript. */
function aor_consulting_contact_error( $message, $status ) {
	if ( aor_consulting_wants_json() ) {
		wp_send_json_error( array( 'message' => $message ), $status );
	}
	wp_die( esc_html( $message ), '', array( 'response' => $status, 'back_link' => true ) );
}

/** Validate before sanitizing email so malformed addresses are never rewritten. */
function aor_consulting_contact_fields( $input ) {
	$fields = array();
	foreach ( array( 'contact_name', 'contact_email', 'contact_message' ) as $key ) {
		if ( ! isset( $input[ $key ] ) || ! is_string( $input[ $key ] ) ) {
			return new WP_Error( 'invalid_fields', __( 'Veuillez renseigner votre nom, votre adresse e-mail et votre message.', 'aor-consulting' ) );
		}
		$fields[ $key ] = trim( wp_unslash( $input[ $key ] ) );
	}

	if ( '' === $fields['contact_name'] || strlen( $fields['contact_name'] ) > 240 ||
		strlen( $fields['contact_email'] ) > 254 || ! is_email( $fields['contact_email'] ) ||
		preg_match( '/[\r\n]/', $fields['contact_email'] ) ||
		'' === $fields['contact_message'] || strlen( $fields['contact_message'] ) > 20000 ) {
		return new WP_Error( 'invalid_fields', __( 'Vérifiez les champs du formulaire et indiquez une adresse e-mail valide.', 'aor-consulting' ) );
	}

	$fields['contact_name']    = sanitize_text_field( $fields['contact_name'] );
	$fields['contact_message'] = sanitize_textarea_field( $fields['contact_message'] );
	if ( '' === $fields['contact_name'] || '' === $fields['contact_message'] ) {
		return new WP_Error( 'invalid_fields', __( 'Le nom et le message ne peuvent pas être vides.', 'aor-consulting' ) );
	}
	$subject = $input['contact_subject'] ?? '';
	if ( ! is_string( $subject ) || ( '' !== $subject && ! array_key_exists( $subject, aor_consulting_contact_subjects() ) ) ) {
		return new WP_Error( 'invalid_subject', __( 'Choisissez un sujet dans la liste proposée.', 'aor-consulting' ) );
	}
	$fields['contact_subject'] = $subject;
	return $fields;
}

/** The recipient is the configured site administrator; no public address is invented. */
function aor_consulting_submit_contact() {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		aor_consulting_contact_error( __( 'Méthode non autorisée.', 'aor-consulting' ), 405 );
	}

	$nonce = isset( $_POST['aor_contact_nonce'] ) && is_string( $_POST['aor_contact_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['aor_contact_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'aor_contact' ) ) {
		aor_consulting_contact_error( __( 'Le formulaire a expiré. Copiez votre message, actualisez la page puis réessayez.', 'aor-consulting' ), 403 );
	}

	if ( ! empty( $_POST['contact_website'] ) ) {
		aor_consulting_contact_error( __( 'Le message n’a pas pu être envoyé.', 'aor-consulting' ), 400 );
	}

	$fields = aor_consulting_contact_fields( $_POST );
	if ( is_wp_error( $fields ) ) {
		aor_consulting_contact_error( $fields->get_error_message(), 400 );
	}

	// Salted, short-lived key: the visitor's IP address is not stored in clear text.
	$rate_key = 'aor_contact_' . wp_hash( $_SERVER['REMOTE_ADDR'] ?? 'unknown' );
	if ( get_transient( $rate_key ) ) {
		aor_consulting_contact_error( __( 'Veuillez patienter une minute avant un nouvel envoi.', 'aor-consulting' ), 429 );
	}
	set_transient( $rate_key, 1, MINUTE_IN_SECONDS );

	$recipient = get_option( 'admin_email' );
	$subject   = aor_consulting_contact_subjects()[ $fields['contact_subject'] ] ?? __( 'Non précisé', 'aor-consulting' );
	$message   = sprintf( "Nom : %s\nE-mail : %s\nSujet : %s\n\n%s", $fields['contact_name'], $fields['contact_email'], $subject, $fields['contact_message'] );
	$sent      = is_email( $recipient ) && wp_mail(
		$recipient,
		__( 'Nouveau message — AO-Risk Consulting', 'aor-consulting' ),
		$message,
		array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $fields['contact_email'] )
	);

	if ( ! $sent ) {
		aor_consulting_contact_error( __( 'L’envoi a échoué. Votre message n’a pas été transmis. Réessayez dans quelques instants.', 'aor-consulting' ), 503 );
	}
	if ( aor_consulting_wants_json() ) {
		wp_send_json_success( array( 'message' => __( 'Merci pour votre message. Votre demande a bien été prise en compte.', 'aor-consulting' ) ) );
	}

	wp_safe_redirect( add_query_arg( 'contact', 'sent', home_url( '/' ) ) . '#contact', 303 );
	exit;
}
add_action( 'admin_post_aor_contact', 'aor_consulting_submit_contact' );
add_action( 'admin_post_nopriv_aor_contact', 'aor_consulting_submit_contact' );

/** Render at request time, including the nonce (never persist it in a pattern). */
function aor_consulting_contact_form() {
	$id = wp_unique_id( 'aor-contact-' );
	ob_start();
	?>
	<?php if ( isset( $_GET['contact'] ) && 'sent' === $_GET['contact'] ) : ?>
		<p class="aor-form-notice" role="status"><?php esc_html_e( 'Merci pour votre message. Votre demande a bien été prise en compte.', 'aor-consulting' ); ?></p>
	<?php endif; ?>
	<form class="aor-contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="aor_contact">
		<?php wp_nonce_field( 'aor_contact', 'aor_contact_nonce', false ); ?>
		<div class="aor-form-row">
			<div class="aor-form-field">
				<label for="<?php echo esc_attr( $id ); ?>name"><?php esc_html_e( 'Votre nom', 'aor-consulting' ); ?></label>
				<input id="<?php echo esc_attr( $id ); ?>name" name="contact_name" autocomplete="name" maxlength="80" required>
			</div>
			<div class="aor-form-field">
				<label for="<?php echo esc_attr( $id ); ?>email"><?php esc_html_e( 'Votre e-mail', 'aor-consulting' ); ?></label>
				<input id="<?php echo esc_attr( $id ); ?>email" name="contact_email" type="email" autocomplete="email" maxlength="254" required>
			</div>
		</div>
		<div class="aor-form-field">
			<label for="<?php echo esc_attr( $id ); ?>subject"><?php esc_html_e( 'Votre besoin (facultatif)', 'aor-consulting' ); ?></label>
			<select id="<?php echo esc_attr( $id ); ?>subject" name="contact_subject">
				<option value=""><?php esc_html_e( 'Choisir un sujet', 'aor-consulting' ); ?></option>
				<?php foreach ( aor_consulting_contact_subjects() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="aor-form-field">
			<label for="<?php echo esc_attr( $id ); ?>message"><?php esc_html_e( 'Parlez-nous de votre projet', 'aor-consulting' ); ?></label>
			<textarea id="<?php echo esc_attr( $id ); ?>message" name="contact_message" rows="5" maxlength="5000" required></textarea>
		</div>
		<div class="aor-form-trap" aria-hidden="true">
			<label for="<?php echo esc_attr( $id ); ?>website">Website</label>
			<input id="<?php echo esc_attr( $id ); ?>website" name="contact_website" tabindex="-1" autocomplete="off">
		</div>
		<p class="aor-form-note"><?php esc_html_e( 'Nom, e-mail et message sont obligatoires. Vos coordonnées servent à répondre à votre demande.', 'aor-consulting' ); ?>
			<?php if ( get_privacy_policy_url() ) : ?>
				<a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'Confidentialité', 'aor-consulting' ); ?></a>
			<?php endif; ?>
		</p>
		<p class="aor-form-notice aor-form-feedback" role="status" aria-live="polite" tabindex="-1" hidden></p>
		<button class="wp-element-button aor-submit" type="submit"><?php esc_html_e( 'Envoyer le message', 'aor-consulting' ); ?> <span aria-hidden="true">↗</span></button>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'aor_contact', 'aor_consulting_contact_form' );

// Patterns in block templates can be expanded after the usual shortcode pass.
function aor_consulting_render_contact_shortcode( $content ) {
	return has_shortcode( $content, 'aor_contact' ) ? do_shortcode( $content ) : $content;
}
add_filter( 'render_block_core/shortcode', 'aor_consulting_render_contact_shortcode' );
