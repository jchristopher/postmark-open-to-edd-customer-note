<?php

/**
 * Plugin Name: Postmark Open to EDD Customer Note
 * Plugin URI:  https://github.com/jchristopher/postmark-open-to-edd-customer-note
 * Description: Add a Customer Note when a Postmark-delivered email has been opened by an EDD Customer
 * Author:      Jonathan Christopher
 * Author URI:  http://mondaybynoon.com/
 * Version:     1.0.0
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class PmeddcnPostmarkOpenToEddCustomerNote {

	private $postmark_api_key = '';

	function __construct() {}

	function init() {
		add_action( 'init', array( $this, 'load' ) );
	}

	function load() {
		$this->postmark_api_key = apply_filters( 'pmeddcn_postmark_api_key', $this->postmark_api_key );

		// We depend on both EDD (2.6+) and Postmark Approved, so unless they're active there's nothing to do
		if ( ! class_exists( 'EDD_Customer' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice__edd' ) );

			return;
		}

		if ( ! class_exists( 'Postmark_Mail' ) && empty( $this->postmark_api_key ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice__postmark_approved' ) );

			return;
		}

		// Retrieve Postmark API key
		$postmark_for_wordpress = new Postmark_Mail();
		$this->postmark_api_key = ! empty( $postmark_for_wordpress->settings['api_key'] ) ? $postmark_for_wordpress->settings['api_key'] : '';

		add_action( 'rest_api_init', function () {
			$rest_route = apply_filters( 'pmeddcn_rest_route', 'pmeddcn/v1' );
			$rest_slug  = apply_filters( 'pmeddcn_rest_slug', '/open' );
			register_rest_route( $rest_route, $rest_slug, array(
				'methods'  => 'POST',
				'callback' => array( $this, 'log_open' ),
			) );
		} );
	}

	/**
	 * Callback for Postmark Webhook
	 *
	 * @since  1.0.0
	 *
	 * @param  WP_REST_Request $request The REST Request
	 */
	function log_open( WP_REST_Request $request ) {
		$parameters = $request->get_json_params();

		$customer_email      = isset( $parameters['Recipient'] ) ? $parameters['Recipient'] : false;
		$postmark_message_id = isset( $parameters['MessageID'] ) ? $parameters['MessageID'] : false;

		if ( ! is_email( $customer_email ) ) {
			return;
		}

		if ( empty( $postmark_message_id ) ) {
			return;
		}

		$customer = new EDD_Customer( $customer_email );

		if ( empty( $customer->id ) ) {
			// Customer does not exist...
			return;
		}

		$email_details = $this->get_postmark_message_details( $postmark_message_id );

		if ( is_null( $email_details ) ) {
			$customer->add_note( esc_html( 'Customer read email [' . $postmark_message_id . '] but message details could not be retrieved' ) );
		} else {
			$customer->add_note( esc_html( 'Customer read email [' . $email_details->Subject . '] with message id [' . $postmark_message_id ) . ']' );
		}
	}

	/**
	 * Utilize the Postmark API to retrieve message details, specifically so we can log
	 * the email subject of the opened email, else we won't know which message was opened
	 *
	 * @since  1.0.0
	 *
	 * @param  string     $postmark_message_id The Postmark Message ID
	 * @return null|array                      The API response
	 */
	function get_postmark_message_details( $postmark_message_id ) {
		$email_details_response = wp_remote_get( 'https://api.postmarkapp.com/messages/outbound/' . $postmark_message_id . '/details',
			array(
				'headers' => array(
					'Accept'                  => 'application/json',
					'X-Postmark-Server-Token' => $this->postmark_api_key,
				),
			)
		);

		$email_details_response_code = wp_remote_retrieve_response_code( $email_details_response );

		if ( 200 !== $email_details_response_code ) {
			return null;
		}

		$email_details = wp_remote_retrieve_body( $email_details_response );
		$email_details = json_decode( $email_details );

		return $email_details;
	}

	/**
	 * Callback to output Admin notice about EDD requirement
	 *
	 * @since  1.0.0
	 */
	function admin_notice_edd() {
		$class = 'notice notice-error';
		$message = __( 'Postmark Open to EDD Customer Note cannot do anything until Easy Digital Downloads 2.6+ is activated.', 'pmeddcn' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	/**
	 * Callback to output Admin notice about Postmark Apprived requirement
	 *
	 * @since  1.0.0
	 */
	function admin_notice__postmark_approved() {
		$class = 'notice notice-error';
		$message = __( 'Postmark Open to EDD Customer Note cannot do anything until Postmark Approved is activated.', 'pmeddcn' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
}

$pmeddcn = new PmeddcnPostmarkOpenToEddCustomerNote();
$pmeddcn->init();
