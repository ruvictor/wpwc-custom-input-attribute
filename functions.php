<?php

/**
 * Storefront automatically loads the core CSS even if using a child theme as it is more efficient
 * than @importing it in the child theme style.css file.
 *
 * Uncomment the line below if you'd like to disable the Storefront Core CSS.
 *
 * If you don't plan to dequeue the Storefront Core CSS you can remove the subsequent line and as well
 * as the sf_child_theme_dequeue_style() function declaration.
 */
//add_action( 'wp_enqueue_scripts', 'sf_child_theme_dequeue_style', 999 );

/**
 * Dequeue the Storefront Parent theme core CSS
 */
function sf_child_theme_dequeue_style() {
    wp_dequeue_style( 'storefront-style' );
    wp_dequeue_style( 'storefront-woocommerce-style' );
}

/**
 * Note: DO NOT! alter or remove the code above this text and only add your custom PHP functions below this text.
 */



// Display the custom text field
function vicode_create_field() {
 $args = array(
 'id' => 'custom_text_field_title',
 'label' => __( 'Additional Field Title', 'vicode' ),
 'class' => 'vicode-custom-field',
 'desc_tip' => true,
 'description' => __( 'Enter the title of your additional custom text field.', 'ctwc' ),
 );
 woocommerce_wp_text_input( $args );
}
add_action( 'woocommerce_product_options_general_product_data', 'vicode_create_field' );



// save data from custom field
function vicode_save_field_data( $post_id ) {
    $product = wc_get_product( $post_id );
    $title = isset( $_POST['custom_text_field_title'] ) ? $_POST['custom_text_field_title'] : '';
    $product->update_meta_data( 'custom_text_field_title', sanitize_text_field( $title ) );
    $product->save();
   }
add_action( 'woocommerce_process_product_meta', 'vicode_save_field_data' );


// Display field on the Product Page
function vicode_display_field() {
    global $post;
    // Check for the custom field value
    $product = wc_get_product( $post->ID );
    $title = $product->get_meta( 'custom_text_field_title' );
    if( $title ) {
    // Display the field if not empty
    printf(
    '<div class="vicode-custom-field-wrapper"><label for="vicode-title-field" style="margin-right: 30px;">%s: </label><input type="text" id="vicode-title-field" name="vicode-title-field" value=""></div><br /><hr />',
    esc_html( $title )
    );
    }
   }
add_action( 'woocommerce_before_add_to_cart_button', 'vicode_display_field' );


// custom input validation
function vicode_field_validation( $passed, $product_id, $quantity ) {
    if( empty( $_POST['vicode-title-field'] ) ) {
    // Fails validation
    $passed = false;
    wc_add_notice( __( 'Please enter a name for your product.', 'vicode' ), 'error' );
    }
    return $passed;
   }
add_filter( 'woocommerce_add_to_cart_validation', 'vicode_field_validation', 10, 3 );


// add field data to the cart
function vicode_add_field_data_to_cart( $cart_item_data, $product_id, $variation_id, $quantity ) {
    if( ! empty( $_POST['vicode-title-field'] ) ) {
    // Add the item data
    $cart_item_data['title_field'] = $_POST['vicode-title-field'];
    $product = wc_get_product( $product_id );
    $price = $product->get_price();
    $cart_item_data['total_price'] = $price;
    }
    return $cart_item_data;
   }
add_filter( 'woocommerce_add_cart_item_data', 'vicode_add_field_data_to_cart', 10, 4 );


// update cart price
function vicode_calculate_cart_totals( $cart_obj ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
    return;
    }
    // Iterate through each cart item
    foreach( $cart_obj->get_cart() as $key=>$value ) {
    if( isset( $value['total_price'] ) ) {
    $price = $value['total_price'];
    $value['data']->set_price( ( $price ) );
    }
    }
   }
add_action( 'woocommerce_before_calculate_totals', 'vicode_calculate_cart_totals', 10, 1 );


// display field in the cart
function vicode_field_to_cart( $name, $cart_item, $cart_item_key ) {
    if( isset( $cart_item['title_field'] ) ) {
    $name .= sprintf(
    '<p>%s</p>',
    esc_html( $cart_item['title_field'] )
    );
    }
    return $name;
   }
add_filter( 'woocommerce_cart_item_name', 'vicode_field_to_cart', 10, 3 );


// Add custom field to order object
function vicode_add_field_data_to_order( $item, $cart_item_key, $values, $order ) {
    foreach( $item as $cart_item_key=>$values ) {
        if( isset( $values['title_field'] ) ) {
        $item->add_meta_data( __( 'Custom Field:', 'vicode' ), $values['title_field'], true );
        }
    }
}
add_action( 'woocommerce_checkout_create_order_line_item', 'vicode_add_field_data_to_order', 10, 4 );