<?php
/**
 * Integration checks against a local WordPress installation. No email is sent.
 * Run: php wp-content/themes/aor-consulting/tests/contact.php
 */
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

require dirname( __DIR__, 4 ) . '/wp-load.php';

if ( ! function_exists( 'aor_consulting_submit_contact' ) ) {
	fwrite( STDERR, "Activate the AOR Consulting theme before running these checks.\n" );
	exit( 1 );
}

class AOR_Test_Response extends RuntimeException {
	public $location;
	public function __construct( $status, $location = '' ) {
		parent::__construct( 'HTTP ' . $status, $status );
		$this->location = $location;
	}
}

$checks      = 0;
$mail_calls  = array();
$mail_result = true;

function aor_test_assert( $condition, $label ) {
	global $checks;
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $label );
	}
	++$checks;
	echo 'PASS: ' . $label . "\n";
}

add_filter( 'wp_die_handler', function () {
	return function ( $message, $title, $args ) {
		throw new AOR_Test_Response( $args['response'] ?? 500 );
	};
} );
add_filter( 'wp_redirect', function ( $location, $status ) {
	throw new AOR_Test_Response( $status, $location );
}, 10, 2 );
add_filter( 'pre_wp_mail', function ( $return, $attributes ) use ( &$mail_calls, &$mail_result ) {
	$mail_calls[] = $attributes;
	return $mail_result; // Short-circuit before PHPMailer; never send an email.
}, PHP_INT_MAX, 2 );

function aor_test_submit( $input, $expected, $label, $method = 'POST' ) {
	$_SERVER['REQUEST_METHOD'] = $method;
	$_POST                    = $input;
	try {
		aor_consulting_submit_contact();
	} catch ( AOR_Test_Response $response ) {
		aor_test_assert( $expected === $response->getCode(), $label );
		return $response;
	}
	throw new RuntimeException( 'No response: ' . $label );
}

// A documentation-only address keeps the test independent of real visitors.
$_SERVER['REMOTE_ADDR'] = '192.0.2.55';
$rate_key              = 'aor_contact_' . wp_hash( $_SERVER['REMOTE_ADDR'] );
$valid                 = array(
	'aor_contact_nonce' => wp_create_nonce( 'aor_contact' ),
	'contact_name'      => 'Élodie Test',
	'contact_email'     => 'elodie@example.org',
	'contact_message'   => "Bonjour,\nUn projet à organiser.",
	'contact_website'   => '',
	'contact_subject'   => 'diagnostic',
);

try {
	delete_transient( $rate_key );
	aor_test_assert( (bool) has_action( 'admin_post_nopriv_aor_contact', 'aor_consulting_submit_contact' ), 'Anonymous visitors can submit' );
	aor_test_assert( (bool) has_action( 'admin_post_aor_contact', 'aor_consulting_submit_contact' ), 'Logged-in visitors can submit' );

	$rendered = do_blocks( '<!-- wp:pattern {"slug":"aor-consulting/call-to-action"} /-->' );
	aor_test_assert( str_contains( $rendered, '<form' ) && ! str_contains( $rendered, '[aor_contact]' ), 'Contact pattern renders a real form' );
	aor_test_assert( str_contains( $rendered, 'name="aor_contact_nonce"' ), 'Rendered form includes a nonce' );

	aor_test_submit( $valid, 405, 'Reject GET requests', 'GET' );
	aor_test_submit( array_replace( $valid, array( 'aor_contact_nonce' => 'invalid' ) ), 403, 'Reject invalid nonce' );
	aor_test_submit( array_replace( $valid, array( 'aor_contact_nonce' => array() ) ), 403, 'Reject array nonce without PHP errors' );
	aor_test_submit( array_replace( $valid, array( 'contact_website' => 'spam' ) ), 400, 'Reject honeypot submissions' );
	aor_test_submit( array_replace( $valid, array( 'contact_email' => "person@example.org\r\nBcc: other@example.org" ) ), 400, 'Reject email header injection' );
	aor_test_submit( array_replace( $valid, array( 'contact_email' => 'not-an-email' ) ), 400, 'Reject malformed email' );
	aor_test_submit( array_replace( $valid, array( 'contact_name' => array() ) ), 400, 'Reject array field values' );
	aor_test_submit( array_replace( $valid, array( 'contact_message' => str_repeat( 'x', 20001 ) ) ), 400, 'Reject oversized messages' );
	aor_test_submit( array_replace( $valid, array( 'contact_message' => '   ' ) ), 400, 'Reject empty messages' );
	aor_test_submit( array_replace( $valid, array( 'contact_name' => '<b></b>' ) ), 400, 'Reject values empty after sanitization' );
	aor_test_submit( array_replace( $valid, array( 'contact_subject' => '<script>alert(1)</script>' ) ), 400, 'Reject subjects outside the allowlist' );
	aor_test_submit( array_replace( $valid, array( 'contact_subject' => array( 'diagnostic' ) ) ), 400, 'Reject array subjects' );
	aor_test_submit( array_replace( $valid, array( 'contact_name' => str_repeat( 'x', 241 ) ) ), 400, 'Reject oversized names' );
	aor_test_submit( array_replace( $valid, array( 'contact_message' => '<img src=x onerror=alert(1)>' ) ), 400, 'Reject markup-only messages' );
	aor_test_assert( 0 === count( $mail_calls ), 'Rejected input never reaches the mail service' );

	$response = aor_test_submit( $valid, 303, 'Accept a valid message and redirect after POST' );
	aor_test_assert( add_query_arg( 'contact', 'sent', home_url( '/' ) ) . '#contact' === $response->location, 'Redirect remains on the site contact section' );
	aor_test_assert( 1 === count( $mail_calls ) && get_option( 'admin_email' ) === $mail_calls[0]['to'], 'Use the configured administrator recipient' );
	aor_test_assert( in_array( 'Reply-To: elodie@example.org', $mail_calls[0]['headers'], true ), 'Replies go to the validated sender' );
	aor_test_assert( str_contains( $mail_calls[0]['message'], 'Élodie Test' ), 'Preserve accented message content' );
	aor_test_assert( str_contains( $mail_calls[0]['message'], 'Diagnostic & cartographie des risques' ), 'Use the allowlisted label in the message body' );
	aor_test_submit( $valid, 429, 'Rate-limit repeated submissions' );
	aor_test_assert( 1 === count( $mail_calls ), 'Rate-limited requests do not send mail' );

	delete_transient( $rate_key );
	$mail_result = false;
	aor_test_submit( $valid, 503, 'Report mail service failures without a success redirect' );
	echo "\n{$checks} checks passed. No email sent.\n";
} finally {
	delete_transient( $rate_key );
}
