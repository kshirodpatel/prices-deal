<?php
class Price_Deals_Of_The_Day {

	public function __construct() {

    //Check if woocommerce plugin is installed.
    add_action( 'admin_notices', array( $this, 'check_required_plugins' ) );

    add_action( 'woocommerce_product_options_general_product_data', array($this, 'pdod_custom_fields') );

    add_action( 'woocommerce_process_product_meta', array($this, 'pdod_save_custom_field') );

    add_action( 'admin_enqueue_scripts', array( $this, 'admin_pdod_scripts') );

    add_action( 'wp_enqueue_scripts', array( $this, 'frontend_pdod_scripts') );


    add_filter( 'woocommerce_get_price_html', array($this, 'pdod_custom_price'), 1, 2 );


    add_action( 'woocommerce_before_calculate_totals', array($this, 'pdod_update_custom_price'), 1, 1 );

    //Add Countdown Timer
    add_action( 'woocommerce_single_product_summary', array( $this, 'pdod_single_product_timer'), 30 );

    add_action( 'wp_ajax_pdod_get_timezone', array($this, 'pdod_get_timezone') );

    add_action( 'wp_ajax_nopriv_pdod_get_timezone', array($this, 'pdod_get_timezone') );

    add_action( 'woocommerce_after_shop_loop_item', array($this, 'pdod_get_shop_page_timer'), 5 );

    add_shortcode( 'wc_prices_deals', array($this, 'prices_deals_shortcode') );
	}

	public function check_required_plugins() {
		//Check if woocommerce is installed and activated
    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) { ?>
    	<div id="message" class="error">
      	<p>WooCommerce Deal Of The Day requires <a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a> to be activated in order to work. Please install and activate <a href="<?php echo admin_url('/plugin-install.php?tab=search&amp;type=term&amp;s=WooCommerce'); ?>" target="">WooCommerce</a> first.</p>
      </div>

    <?php deactivate_plugins( '/prices-deal-of-the-day/prices-deal-of-the-day.php' );
    }
	}

  public function pdod_custom_fields() {
    $args = array(
      'id' => 'pdod_enable',
      'label' => __( 'Enable Deal Price?', 'pdod' ),
      'class' => 'pdod-enable',
      'desc_tip' => true,
      'description' => __( 'Enable checkbox for setting deal price for this product.', 'ctwc' ),
    );
    woocommerce_wp_checkbox( $args );

    $args = array(
      'id' => 'pdod_price',
      'data_type'   => 'price',
      'label'       => __( 'Deal price', 'pdod' ) . ' (' . get_woocommerce_currency_symbol() . ')',
      'class' => 'pdod-price',
      'desc_tip' => true,
      'description' => __( 'Set deal price for this product.', 'ctwc' ),
    );
    woocommerce_wp_text_input( $args );

    $args = array(
      'id' => 'pdod_start_date',
      'label' => __( 'Deal Price Start Date', 'pdod' ),
      'class' => 'pdod-dates',
      'desc_tip' => true,
      'description' => __( 'Set deal price start date.', 'ctwc' ),
    );
    woocommerce_wp_text_input( $args );

    $args = array(
      'id' => 'pdod_end_date',
      'label' => __( 'Deal Price End Date', 'pdod' ),
      'class' => 'pdod-dates',
      'desc_tip' => true,
      'description' => __( 'Set deal price end date.', 'ctwc' ),
    );
    woocommerce_wp_text_input( $args );
  }

  public function pdod_save_custom_field($post_id) {
    $product = wc_get_product( $post_id );
    $enable_pdod = isset( $_POST['pdod_enable'] ) ? $_POST['pdod_enable'] : '';
    $pdod_price = isset( $_POST['pdod_price'] ) ? $_POST['pdod_price'] : '';
    $pdod_start_date = isset( $_POST['pdod_start_date'] ) ? $_POST['pdod_start_date'] : '';
    $pdod_end_date = isset( $_POST['pdod_end_date'] ) ? $_POST['pdod_end_date'] : '';

    $product->update_meta_data( 'pdod_enable',  $enable_pdod);
    $product->update_meta_data( 'pdod_price',   sanitize_text_field( $pdod_price ));
    $product->update_meta_data( 'pdod_start_date',  sanitize_text_field( $pdod_start_date ));
    $product->update_meta_data( 'pdod_end_date',   sanitize_text_field( $pdod_end_date ));

    $product->save();
  }

  public function admin_pdod_scripts() {
    wp_enqueue_script( 'timepicker',  plugins_url( 'assets/js/jquery-ui-timepicker-addon.js', PDOD_FILE ), array( 'jquery-ui-datepicker' ));
    wp_enqueue_script( 'pdod-admin',  plugins_url( 'assets/js/pdod-admin.js', PDOD_FILE ), array( 'jquery' ), '1.0.0', true );
    wp_enqueue_style ( 'pdod-admin-style', plugins_url( 'assets/css/jquery-ui.css', PDOD_FILE ) );
    wp_enqueue_style ( 'pdod-admin-list-style', plugins_url( 'assets/css/pdod-admin.css', PDOD_FILE ) );
    wp_enqueue_style ( 'pdod-jquery-timepicker', plugins_url( 'assets/css/jquery-ui-timepicker-addon.css', PDOD_FILE ) );

    wp_localize_script( 'pdod-admin', 'pdod', array(
      'date_format'  => get_option('date_format'),
    ));
  }

  public function frontend_pdod_scripts() {
    wp_enqueue_style ( 'pdod-frontend', plugins_url( 'assets/css/pdod-frontend.css', PDOD_FILE ) );

    wp_enqueue_style( 'woocs-countdown-style', plugins_url( 'assets/css/jquery.countdown.css', PDOD_FILE ) );
    
    wp_enqueue_script( 'woocs-custom-script',  plugins_url( 'assets/js/jquery.plugin.js', PDOD_FILE ), array( 'jquery' ), '1.0.0', true );
    
    wp_enqueue_script( 'woocs-countdown',  plugins_url( 'assets/js/jquery.countdown.js', PDOD_FILE ), array( 'jquery' ), '1.0.0', true );

    wp_enqueue_script( 'pdod-frontend',  plugins_url( 'assets/js/pdod-frontend.js', PDOD_FILE ), array( 'jquery' ), '1.0.0', true );

    wp_localize_script( 'pdod-frontend', 'pdodvars', array(
      'date_format'  => get_option('date_format'),
      'ajaxurl'      => admin_url( 'admin-ajax.php' ),
    ));
  }

  public function pdod_get_timezone() {
    $timezoneoffset = !empty($_POST['timezoneOffset']) ? $_POST['timezoneOffset'] : '';
    
    $timezone_name = timezone_name_from_abbr("", $timezoneoffset*60, false);

    setcookie('pdod_timezone', $timezone_name, time() + (86400 * 30), "/");
    exit;
  }

  public function check_valid_deal( $product_id ) {
    if( $product_id !== '' ) {
      $enabled_deal = get_post_meta( $product_id, 'pdod_enable', true );
      $deal_start_date = get_post_meta( $product_id, 'pdod_start_date', true );
      $deal_start_date = strtotime($deal_start_date);

      $deal_end_date = get_post_meta( $product_id, 'pdod_end_date', true );
      $deal_end_date = strtotime($deal_end_date);

      $set_date_format = get_option('date_format');
      $set_time_fomat  = get_option('time_format');

      $current_date_time = date($set_date_format.' '.$set_time_fomat);

      $current_date_time = strtotime($current_date_time); 

      if( $enabled_deal == 'yes' ) {
        if( $current_date_time >= $deal_start_date && $current_date_time <  $deal_end_date ) {
          return true;
        }
      }
    }
  }

  public function get_product_deal_price($product_id) {
    if( $product_id !== '' ) {
      $deal_price   =  get_post_meta( $product_id, 'pdod_price', true );
      return $deal_price;
    }
  }

  public function pdod_update_custom_price($cart_object) {
    foreach ( $cart_object->cart_contents as $cart_item_key => $value ) {
      $product_id = isset($value['product_id']) ? $value['product_id'] : '';
      
      if( $this->check_valid_deal( $product_id ) ) {
        $price = $this->get_product_deal_price($product_id);
        $value['data']->set_price(wc_price($price));
      }
    }
  }

  public function pdod_custom_price($price_html, $product) {
    $product_id = get_the_id();
    $valid_product = $this->check_valid_deal($product_id);

    if( $valid_product ) {
      $product_price = get_post_meta( $product_id, 'pdod_price', true );
      $product = wc_get_product( $product_id );
      $product_regular_price = $product->get_regular_price();
      $product_sale_price = $product->get_sale_price();
      $product_default_price = $product->get_price();

      if( !empty($product_default_price) ) {
        $product_get_price = $product_default_price;
      }
      elseif (!empty($product_sale_price)) {
        $product_get_price = $product_sale_price;
      }
      else {
        $product_get_price = $product_regular_price;
      }
      $price_html = '<span class="pdod-strike-price">'.wc_price($product_get_price).'</span>';
      $price_html .= wc_price($product_price);

    }
    return $price_html;
  }

  public function pdod_single_product_timer() {
    global $product;
    $product_id = !empty( get_the_ID() ) ? get_the_ID() : $product->get_id();
    $this->show_deal_timer($product_id);
  }

  public function show_deal_timer($product_id) {
    if( !empty($product_id) ) {
      $valid_product = $this->check_valid_deal($product_id);

      if( $valid_product ) {
        $set_date_format = get_option('date_format');
        $set_time_fomat  = get_option('time_format');

        $current_date_time = date($set_date_format.' '.$set_time_fomat);
      
        $current_date_time = strtotime($current_date_time);

        $orig_end_date = get_post_meta( $product_id, 'pdod_end_date', true );
        $deal_end_date = strtotime($orig_end_date);

        $curr_date = date("F j, Y G:i", $current_date_time);
        $timezone = get_option('timezone_string');
        $deal_end_date = date("F j, Y G:i", $deal_end_date);


        if( isset($_COOKIE['pdod_timezone']) ) {
          $dt = new DateTime($orig_end_date);
          $tz = new DateTimeZone($_COOKIE['pdod_timezone']); // or whatever zone you're after
          $dt->setTimezone($tz);
          $deal_end_date = $dt->format('F j, Y G:i');
        }
        
        $display_timer = '';
        $display_timer .= '<div class="pdod-wrapper">Ends in<div class="pdod-timer" data-time-zone="'.$timezone.'" data-orig-end-date="'.$orig_end_date.'" data-pdod-end="'.$deal_end_date.'" data-curr-date="'.$curr_date.'" data-product-id="'.$product_id.'"></div></div>';
        echo $display_timer;
      }
    }
  }


  public function pdod_get_shop_page_timer() {
    global $product;
    $product_id = !empty( get_the_ID() ) ? get_the_ID() : $product->get_id();
    $this->show_deal_timer($product_id);
  }

  public function prices_deals_shortcode($atts) {
    $options = shortcode_atts( array(
      'product_count' => -1,
      'exclude_category' => '',
      'exclude_product' => '',
      'btn_width' => 'auto',
        ), $atts );
    extract( $options );

    $products = $this->pdod_get_deal_products( $options );
    return $products;
  }

  public function pdod_get_deal_products( $options = array() ) {

    $product_numbers = -1;
    $price_deals_of_the_day = 'deals_for_today';

    $args = array(
      'post_type'           => 'product',
      'post_status'         => 'publish',
      'posts_per_page'      => $product_numbers,
      'meta_query'          => array(
                                array(
                                  'key'   => 'pdod_enable',
                                  'value' => 'yes'
                                ),
      ),
    );

    $products = $this->product_loop( $args, $price_deals_of_the_day );
    return $products;
  }

    /**
  * Loop over found products
  * @param  array $query_args
  * @param  array $atts
  * @param  string $loop_name
  * @return string
  */

  public function product_loop( $query_args, $loop_name ) {
    $product_cols = 3;
    global $woocommerce_loop;
    $products                    = new WP_Query( $query_args );
    $columns                     = absint( $product_cols );
    $woocommerce_loop['columns'] = $columns;


    ob_start();
    if ( $products->have_posts() ) : ?>

      <?php woocommerce_product_loop_start(); ?>

        <?php while ( $products->have_posts() ) : $products->the_post(); ?>
         
          <?php wc_get_template_part( 'content', 'product' ); ?>

        <?php endwhile; // end of the loop. ?>

      <?php woocommerce_product_loop_end(); ?>

    <?php endif;

    woocommerce_reset_loop();
    wp_reset_postdata();

    return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
  }

}