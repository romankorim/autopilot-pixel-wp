<?php
/**
 * Plugin Name: Autopilot Pixel
 * Plugin URI:  https://app.iteractive.ai
 * Description: Auto-installed by Autopilot. Fires Meta Pixel on page views and forwards WooCommerce purchase events to Meta Conversions API server-side.
 * Version:     1.0.0
 * Author:      iteractive.ai
 * Author URI:  https://iteractive.ai
 * License:     GPLv2 or later
 * Text Domain: autopilot-pixel
 */

defined('ABSPATH') || exit;

const AUTOPILOT_PIXEL_VERSION  = '1.0.0';
const AUTOPILOT_OPTION_PIXEL   = 'autopilot_pixel_id';
const AUTOPILOT_OPTION_USER    = 'autopilot_user_id';
const AUTOPILOT_CONVERSION_URL = 'https://app.iteractive.ai/functions/v1/wp-conversion';

/**
 * Render the Meta Pixel snippet in <head> of every page.
 */
add_action('wp_head', function () {
    $pixel_id = get_option(AUTOPILOT_OPTION_PIXEL, '');
    if (empty($pixel_id)) {
        return;
    }
    $pixel_id = esc_attr($pixel_id);
    ?>
<!-- Meta Pixel (auto-installed by Autopilot v<?php echo esc_attr(AUTOPILOT_PIXEL_VERSION); ?>) -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?php echo $pixel_id; ?>');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo $pixel_id; ?>&ev=PageView&noscript=1"/></noscript>
<!-- End Autopilot Pixel -->
    <?php
});

/**
 * WooCommerce: forward completed orders to Meta Conversions API server-side.
 */
add_action('woocommerce_order_status_completed', function ($order_id) {
    $pixel_id = get_option(AUTOPILOT_OPTION_PIXEL, '');
    $user_ref = get_option(AUTOPILOT_OPTION_USER, '');
    if (empty($pixel_id) || !function_exists('wc_get_order')) {
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    $payload = [
        'pixel_id'   => $pixel_id,
        'user_ref'   => $user_ref,
        'event_name' => 'Purchase',
        'value'      => (float) $order->get_total(),
        'currency'   => $order->get_currency(),
        'order_id'   => (string) $order_id,
        'site_url'   => get_site_url(),
        'email_hash' => hash('sha256', strtolower(trim($order->get_billing_email()))),
        'timestamp'  => time(),
    ];

    wp_remote_post(AUTOPILOT_CONVERSION_URL, [
        'timeout'  => 5,
        'blocking' => false, // fire-and-forget; never block checkout
        'body'     => wp_json_encode($payload),
        'headers'  => [
            'content-type' => 'application/json',
            'user-agent'   => 'autopilot-pixel/' . AUTOPILOT_PIXEL_VERSION,
        ],
    ]);
});

/**
 * Tiny admin notice on plugin activation so the customer knows what landed.
 */
register_activation_hook(__FILE__, function () {
    if (!get_option(AUTOPILOT_OPTION_PIXEL)) {
        add_option(AUTOPILOT_OPTION_PIXEL, '');
    }
});
