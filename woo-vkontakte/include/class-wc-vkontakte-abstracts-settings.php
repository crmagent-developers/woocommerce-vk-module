<?php
/**
 * PHP version 5.3
 *
 * @category Integration
 */


class WC_VKontakte_Abstracts_Settings extends WC_Integration
{
	//prefix for options
	const ORDER_STATUSES      = 'os-';
	const TYPES_OF_DELIVERIES = 'tod-';
	const TYPES_OF_PAYMENTS   = 'top-';
	const CATEGORY_LIST       = 'cl-';
	const CATEGORY_CONFORMITY = 'cc-';

	const YES = 'yes';
	const NO  = 'no';

	/** @var string */
	public static $option_key;

	/** @var string */
	public static $token_user;

	/** @var string */
	public static $token_group;

	/** @var array */
	public static $options_oauth;

	/** @var array */
	public static $options_event;

	/**
	 * WC_VKontakte_Abstracts_Settings constructor.
	 */
	public function __construct() {
		$this->id                 = 'integration-vkontakte';
		$this->method_title       = __( 'VKontakte', 'vkontakte' );
		$this->method_description = __( 'Integration with VKontakte.', 'vkontakte' );

	  static::$option_key     = $this->get_option_key();
	  static::$token_user     = get_option( 'vkontakte_token_user' );
	  static::$token_group    = get_option( 'vkontakte_token_group' );
		static::$options_oauth  = get_option( 'vkontakte_oauth_settings' );
		static::$options_event  = get_option( 'vkontakte_events' );

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings'
		    && isset( $_GET['tab'] ) && $_GET['tab'] == 'integration'
		) {
		    add_action( 'init', array( $this, 'init_settings_fields' ), 99 );
		}
	}

	/**
   * Ajax
   */

	public function ajax_get_token_user()
	{
		$ajax_url = admin_url('admin-ajax.php');

		?>

			<script type="text/javascript">
        jQuery('#vkontakte_get_token_user').bind('click', function() {
          jQuery.ajax({
            type: "POST",
            url: '<?php echo $ajax_url; ?>?action=get_token_user',
            success: function (response) {
              window.location.href = response;
            }
          });
        });
			</script>

		<?php
	}

	public function ajax_get_token_group()
	{
		$ajax_url = admin_url('admin-ajax.php');

		?>

			<script type="text/javascript">
        jQuery('#vkontakte_get_token_group').bind('click', function() {
          jQuery.ajax({
            type: "POST",
            url: '<?php echo $ajax_url; ?>?action=get_token_group',
            success: function (response) {
              window.location.href = response;
            }
          });
        });
			</script>

		<?php
	}

	public function ajax_subscribe_to_vk_events()
	{
		$ajax_url = admin_url('admin-ajax.php');

		?>

			<script type="text/javascript">
        jQuery('#vkontakte_subscribe_to_events').bind('click', function() {
          jQuery.ajax({
            type: "POST",
            url: '<?php echo $ajax_url; ?>?action=subscribe_to_vk_events',
            beforeSend: function() {
							jQuery('#vkontakte_subscribe_to_events').attr('disabled', true);
						},
            success: function (response) {
              if (response) {
								location.reload();
              }
            }
          });
        });
			</script>

		<?php
	}

	public function ajax_unsubscribe_to_vk_events()
	{
		$ajax_url = admin_url('admin-ajax.php');

		?>

			<script type="text/javascript">
	      jQuery('#vkontakte_unsubscribe_to_events').bind('click', function() {
          jQuery.ajax({
            type: "POST",
            url: '<?php echo $ajax_url; ?>?action=unsubscribe_to_vk_events',
            beforeSend: function() {
							jQuery('#vkontakte_unsubscribe_to_events').attr('disabled', true);
						},
            success: function (response) {
              if (response) {
								location.reload();
              }
            }
          });
	      });
			</script>

		<?php
	}

	public function ajax_clear_vk_logs()
	{
		$ajax_url = admin_url('admin-ajax.php');

		?>

			<script type="text/javascript">
	      jQuery('#clear_vk_logs').bind('click', function() {
          jQuery.ajax({
            type: "POST",
            url: '<?php echo $ajax_url; ?>?action=clear_vk_logs',
            beforeSend: function() {
							jQuery('#clear_vk_logs').attr('disabled', true);
						},
            success: function (response) {
              if (response) {
								location.reload();
              }
            }
          });
	      });
			</script>

		<?php
	}

	/**
	 * Initialize integration settings form fields from oauth.
	 */
	public function init_form_fields_oauth() {

	  $this->form_fields = array(
		  array(
		  	'title' => __( 'Authorization settings', 'vkontakte' ),
			  'type'  => 'title',
			  'desc'  => '',
			  'id'    => 'general_options'
		  ),

		  'id_application' => array(
			  'title'             => __( 'ID application', 'vkontakte' ),
			  'type'              => 'text',
			  'description'       => '',
			  'desc_tip'          => true,
			  'default'           => ''
		  ),
		  'secret_key' => array(
			  'title'             => __( 'Secret key', 'vkontakte' ),
			  'type'              => 'text',
			  'description'       => '',
			  'desc_tip'          => true,
			  'default'           => ''
		  ),
		  'id_group' => array(
			  'title'             => __( 'ID group', 'vkontakte' ),
			  'type'              => 'text',
			  'description'       => '',
			  'desc_tip'          => true,
			  'default'           => ''
		  )
	  );

	  if ($this->check_options_oauth($this->get_new_option())) {

			if (empty(static::$token_user)) {
			  $this->form_fields[] = array(
				  'label'       => __( 'Get a tokens', 'vkontakte' ),
				  'title'       => __( 'Get user token', 'vkontakte' ),
				  'type'        => 'button',
				  'description' => '',
				  'desc_tip'    => false,
				  'id'          => 'vkontakte_get_token_user'
			  );
		  }

	    if (empty(static::$token_group)) {
		    $this->form_fields[] = array(
			    'label'       => __( 'Get a tokens', 'vkontakte' ),
			    'title'       => __( 'Get a group token', 'vkontakte' ),
			    'type'        => 'button',
			    'description' => '',
			    'desc_tip'    => false,
			    'id'          => 'vkontakte_get_token_group'
		    );
	    }
	  }
	}

	/**
	 * Initialize integration settings form fields.
	 */
	public function init_form_fields()
	{
		$this->form_fields = array();

		if (isset($_GET['page']) && $_GET['page'] == 'wc-settings'
		    && isset($_GET['tab']) && $_GET['tab'] == 'integration'
		) {
			add_action('admin_print_footer_scripts', array($this, 'show_blocks'), 99);
			add_action('admin_print_footer_scripts', array($this, 'show_categories'), 99);
			add_action('admin_print_footer_scripts', array($this, 'checked_checkbox_categories'), 99);
			add_action('admin_print_footer_scripts', array($this, 'checked_select_categories'), 99);
			add_action('admin_print_footer_scripts', array($this, 'count_selected_products'), 99);

			if (0 === static::$options_event['status'] || empty(static::$options_event['server_id'])) {
				$this->form_fields[] = array(
			    'label'       => __( 'Subscribe', 'vkontakte' ),
			    'title'       => __( 'VKontakte events', 'vkontakte' ),
			    'type'        => 'button',
			    'description' => '',
			    'css'         => 'min-width:120px;',
			    'desc_tip'    => false,
			    'id'          => 'vkontakte_subscribe_to_events'
		    );
			} else {
				$this->form_fields[] = array(
			    'label'       => __( 'Unsubscribe', 'vkontakte' ),
			    'title'       => __( 'VKontakte events', 'vkontakte' ),
			    'type'        => 'button',
			    'description' => '',
			    'css'         => 'color: red; min-width:120px;',
			    'desc_tip'    => false,
			    'id'          => 'vkontakte_unsubscribe_to_events'
		    );
			}

			/**
			 * Statuses options
			 */
			$vkontakte_statuses_list = $this->references->VK_getOrderStatuses();
			$wc_statuses = $this->references->WC_getOrderStatuses();

			$this->form_fields[] = array(
				'title'       => __('Order statuses', 'vkontakte'),
				'type'        => 'heading',
				'description' => '',
				'id'          => 'statuses_options'
			);

			foreach ($vkontakte_statuses_list as $status_id => $status_name) {
				$this->form_fields[self::ORDER_STATUSES . $status_id] = array(
					'title'    => $status_name,
					'css'      => 'min-width:350px;',
					'class'    => 'select',
					'type'     => 'select',
					'options'  => $wc_statuses,
					'desc_tip' => true
				);
			}

			$this->form_fields[self::ORDER_STATUSES . 'default'] = array(
				'title'          => '',
				'description'    => __('Select the default order status', 'vkontakte'),
				'css'            => 'min-width:350px;',
				'class'          => 'select',
				'type'           => 'select',
				'default'        => 0,
				'options'        => $wc_statuses
			);

			/**
			 * Shipping options
			 */

			$shipping_option_list = $this->references->VK_getDeliveryTypes();
			$wc_shipping_list = $this->references->WC_getDeliveryTypes();

			$this->form_fields[] = array(
				'title'       => __('Delivery types', 'vkontakte'),
				'type'        => 'heading',
				'description' => '',
				'id'          => 'shipping_options'
			);

			foreach ($shipping_option_list as  $shipping_id => $shipping_name) {
				$this->form_fields[self::TYPES_OF_DELIVERIES . $shipping_id] = array(
					'title'          => $shipping_name,
					'css'            => 'min-width:350px;',
					'class'          => 'select',
					'type'           => 'select',
					'options'        => $wc_shipping_list
				);
			}

			$this->form_fields[self::TYPES_OF_DELIVERIES . 'default'] = array(
				'title'          => '',
				'description'    => __('Choose the default shipping type', 'vkontakte'),
				'css'            => 'min-width:350px;',
				'class'          => 'select',
				'type'           => 'select',
				'default'        => 0,
				'options'        => $wc_shipping_list
			);

			/**
			 * Payments
       */

			$wc_payment_list = $this->references->WC_getPaymantTypes();

			$this->form_fields[] = array(
				'title'       => __('Payments types', 'vkontakte'),
				'type'        => 'heading',
				'description' => '',
				'id'          => 'payments_options'
			);

			$this->form_fields[self::TYPES_OF_PAYMENTS . 'default'] = array(
				'title'          => '',
				'description'    => __('Select the default payments type', 'vkontakte'),
				'css'            => 'min-width:350px;',
				'class'          => 'select',
				'type'           => 'select',
				'default'        => 0,
				'options'        => $wc_payment_list
			);

			/**
			 * Import
       */

			$this->form_fields[] = array(
        'title'       => __( 'Import settings', 'vkontakte' ),
        'type'        => 'heading',
        'description' => '',
        'id'          => 'import_settings'
	    );

			$this->form_fields['item_status_import'] = array(
				'title'          => __('Item status', 'vkontakte'),
				'css'            => 'min-width:350px;',
				'class'          => 'select',
				'type'           => 'select',
				'default'        => 0,
				'description'    => __('Assign the selected status to the uploaded products', 'vkontakte'),
				'desc_tip'       => true,
				'options'        => get_post_statuses()
			);

			$this->form_fields['import'] = array(
        'title'       => __('Enable import', 'vkontakte'),
        'label'       => ' ',
        'description' => __('Starting a one-time import of goods on a schedule', 'vkontakte'),
        'class'       => 'checkbox',
        'type'        => 'checkbox',
        'desc_tip'    => true
      );

			/**
			 * Export
       */

			$this->form_fields[] = array(
        'title'       => __( 'Export settings', 'vkontakte' ),
        'type'        => 'heading',
        'description' => '',
        'id'          => 'export_settings'
	    );

			$this->form_fields['item_status_export'] = array(
			    'label'       => ' ',
			    'title'       => __('Item status', 'vkontakte'),
			    'class'       => '',
			    'type'        => 'multiselect',
			    'description' => __('Unload goods only with selected statuses', 'vkontakte'),
			    'desc_tip'    => true,
			    'options'     => get_post_statuses(),
			    'select_buttons' => true
			);

			$this->form_fields['album_by_parent_export'] = array(
        'title'       => __('Albums by parent category', 'vkontakte'),
        'label'       => ' ',
        'description' => '',
        'class'       => 'checkbox',
        'type'        => 'checkbox',
        'desc_tip'    => true
      );

			$this->form_fields['export'] = array(
        'title'       => __('Enable export', 'vkontakte'),
        'label'       => ' ',
        'description' => __('Starting permanent export of goods on a schedule', 'vkontakte'),
        'class'       => 'checkbox',
        'type'        => 'checkbox',
        'desc_tip'    =>  true
      );

			$wc_categories = $this->references->WC_getCategories(array(
				'pad_counts'    => 1,
				'custom_fields' => ['term_id', 'name', 'count', 'level', 'parent', 'childless'],
				'custom_sort'   => 'said-bar'
			));

			$vk_categories = $this->references->VK_getCategories(array(
				'custom_sort' => 'sections'
			));

			$this->form_fields[] = array(
				'title'       => __('Export categories', 'vkontakte'),
				'type'        => 'title_categories_options'
			);

			foreach ($wc_categories as $wc_category) {
				$this->form_fields[self::CATEGORY_LIST . $wc_category['term_id']] = array(
					'type'        => 'checkbox_categories_options',
					'wc_category' => $wc_category,
				);

				$this->form_fields[self::CATEGORY_CONFORMITY . $wc_category['term_id']] = array(
					'type'          => 'select_categories_options',
					'wc_category'   => $wc_category,
					'vk_categories' => $vk_categories,
				);
			}

			$this->form_fields[] = array(
				'title'       => __('Selected products', 'vkontakte'),
				'type'        => 'selected_products_categories_options'
			);

			/**
			 * Logs
       */

			$logsData = WC_VKontakte_Base::get_vk_logs();

			$this->form_fields[] = array(
		    'title' => __('Error log', 'vkontakte'),
			  'type'  => 'title',
			  'desc'  => '',
			  'id'    => 'vk_error_log'
		  );

			$this->form_fields[] = array(
				'short_log'       => $logsData['short_log'],
				'detail_log_path' => $logsData['detail_log_path'],
				'error'           => $logsData['error'],
				'type'            => 'vk_logs_view'
			);
		}
	}

	/**
   * View
   */

	/**
	 * Generate html title categories options
	 *
	 * @param $key
	 * @param $data
	 *
	 * @return false|string
	 */
	public function generate_title_categories_options_html($key, $data)
	{
		$field_key = $this->get_field_key( $key );

    $defaults  = array(
			'title' => '',
			'class' => '',
		);

    $data = wp_parse_args( $data, $defaults );

    ob_start();
    ?>

      </table>
			<h3 class="wc-settings-sub-title <?php echo esc_attr( $data['class'] ); ?>" id="<?php echo esc_attr( $field_key ); ?>">
				<?php echo wp_kses_post( $data['title'] ); ?>
			</h3>
			<div style="max-width: 1000px; margin: auto;">
				<div style="max-height: 500px; overflow: auto;">
				<hr>

    <?php
    return ob_get_clean();
	}

	/**
	 * Generate html selection of WC categories
	 *
	 * @param $key
	 * @param $data
	 *
	 * @return false|string
	 */
	public function generate_checkbox_categories_options_html($key, $data)
	{
    $field_key = $this->get_field_key( $key );

	  $defaults  = array(
			'wc_category' => array(
					'term_id' => '',
					'name'    => '',
					'count'   => 0
				),
		);

    $data = wp_parse_args( $data, $defaults );

    ob_start();
    ?>

	      <div  class="row-category"
		          style=" display: flex;
						          justify-content: space-between;
						          align-items: center;
						          padding-left: 30px;
						          /*padding-left: */<?php //echo 30 + 15 * $data['wc_category']['level'] ?>/*px;*/
						          position: relative;"
		          data-id="<?php echo esc_attr( $data['wc_category']['term_id'] ); ?>"
			        data-parent="<?php echo esc_attr( $data['wc_category']['parent'] ); ?>">
					<?php if ( $data['wc_category']['childless'] ) : ?>
	        <span class="hidden-category flag-show" style="opacity: 0.5; float: right; position: absolute; left: 5px">&#11014;</span>
          <?php endif; ?>
					<label>
						<?php //echo '<span style="margin-right: 5px">' . wp_kses_post( str_repeat('— ', $data['wc_category']['level']) ) . '</span>'; ?>
						<input
							class="selection-of-categories"
							type="checkbox"
							name="<?php echo esc_attr( $field_key ); ?>"
							id="<?php echo esc_attr( $field_key ); ?>"
	            value="<?php echo esc_attr( $data['wc_category']['parent'] ); ?>"
	            data-id="<?php echo esc_attr( $data['wc_category']['term_id'] ); ?>"
	            data-parent="<?php echo esc_attr( $data['wc_category']['parent'] ); ?>"
	            <?php if ($this->get_option( $key ) !== "") {echo " checked='checked'";} ?>
	          >
            <?php echo wp_kses_post( str_repeat('— ', $data['wc_category']['level']) . $data['wc_category']['name'] ); ?>
						<?php //echo wp_kses_post( $data['wc_category']['name'] ); ?>
            <span class="count-category-<?php echo esc_attr( $data['wc_category']['term_id'] ); ?> count-products"
                  style="color: grey;"
            >
              <?php echo wp_kses_post( ' (' . $data['wc_category']['count'] . ')' ); ?>
            </span>
					</label>

    <?php
    return ob_get_clean();
	}

	/**
	 * Generate html selection of VK categories
	 *
	 * @param $key
	 * @param $data
	 *
	 * @return false|string
	 */
	public function generate_select_categories_options_html($key, $data)
	{
	  $field_key = $this->get_field_key( $key );

    $defaults  = array(
			'wc_category' => array(
					'term_id' => '',
					'name'    => '',
					'count'   => 0
				),
				'vk_categories' => array(
					array(
						'id'          => '',
						'name'        => '',
						'categories'  => array(
							'id'   => '',
							'name' => ''
						)
					)
				)
		);

    $data = wp_parse_args( $data, $defaults );

    ob_start();
    ?>

          <label>
            <select
              class="matching-categories"
              name="<?php echo esc_attr( $field_key ); ?>"
              id="<?php echo esc_attr( $field_key ); ?>"
              data-id="<?php echo esc_attr( $data['wc_category']['term_id'] ); ?>"
	            data-parent="<?php echo esc_attr( $data['wc_category']['parent'] ); ?>"
            >

              <?php foreach($data['vk_categories'] as $vk_section) : ?>
                <option value="<?php echo esc_attr( $vk_section['id'] ); ?>" disabled>
                  <?php echo wp_kses_post( $vk_section['name'] ); ?>
                </option>
                <?php foreach($vk_section['categories'] as $vk_category) : ?>
                  <option value="<?php echo esc_attr( $vk_category['id'] ); ?>"
                  <?php if ($this->get_option( $key ) == (string) $vk_category['id']) {echo " selected='selected'";} ?>
                  >
                    &mdash; <?php echo esc_html($vk_category['name']); ?>
                  </option>
                <?php endforeach; ?>

              <?php endforeach; ?>
            </select>
          </label>
				</div>
        <hr>

    <?php
    return ob_get_clean();
	}

	/**
   * Generate html selected products block
   *
	 * @param $key
	 * @param $data
	 *
	 * @return false|string
	 */
	public function generate_selected_products_categories_options_html($key, $data)
	{
	  $field_key = $this->get_field_key( $key );

    $defaults  = array(
			'title' => ''
		);

    $data = wp_parse_args( $data, $defaults );

    ob_start();
    ?>

			</div>
			<div style="margin-top: 20px; padding-left: 30px">
				<?php echo wp_kses_post( $data['title'] ); ?>
				<span id="<?php echo esc_attr( $field_key ); ?>" class="selected_products_categories_options">(0)</span>
				<span class="error-limit-count" style="display: none; color: red; margin-left: 10px"><?php echo __('Exceeded the limit on the number of products!', 'vkontakte'); ?></span>
			</div>
		</div>
		<table class="form-table">

    <?php
    return ob_get_clean();
	}

  /**
   * Generate html button
   *
   * @param string $key
   * @param array $data
   *
   * @return string
   */
  public function generate_button_html($key, $data)
  {
    $field_key = $this->get_field_key( $key );
    $defaults = array(
      'class'             => 'button-secondary',
      'css'               => '',
      'custom_attributes' => array(),
      'desc_tip'          => false,
      'description'       => '',
      'title'             => '',
    );

    $data = wp_parse_args( $data, $defaults );

    ob_start();
    ?>

      <tr valign="top">
        <th scope="row" class="titledesc">
          <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
          <?php echo $this->get_tooltip_html( $data ); ?>
        </th>
        <td class="forminp">
          <fieldset>
            <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['label'] ); ?></span></legend>
            <button id="<?php echo $data['id']; ?>" class="<?php echo esc_attr( $data['class'] ); ?>" type="button" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php echo $this->get_custom_attribute_html( $data ); ?>><?php echo wp_kses_post( $data['label'] ); ?></button>
            <?php echo $this->get_description_html( $data ); ?>
          </fieldset>
        </td>
      </tr>

    <?php
    return ob_get_clean();
  }

  /**
   * Generate html title block settings
   *
   * @param string $key
   * @param array $data
   *
   * @return string
   */
  public function generate_heading_html($key, $data)
    {
      $field_key = $this->get_field_key( $key );
      $defaults  = array(
        'title' => '',
        'class' => '',
      );

      $data = wp_parse_args( $data, $defaults );

      ob_start();
      ?>

        </table>
        <h3 class="wc-settings-sub-title vkontakte_hidden <?php echo esc_attr( $data['class'] ); ?>" id="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <span style="opacity:0.5;float: right;">&#11015;</span></h3>
        <?php if ( ! empty( $data['description'] ) ) : ?>
            <p><?php echo wp_kses_post( $data['description'] ); ?></p>
        <?php endif; ?>
        <table class="form-table" style="display: none;">

      <?php

      return ob_get_clean();
    }

   /**
   * Generate html viewing the plugin error log VKontakte
   *
   * @param string $key
   * @param array $data
   *
   * @return string
   */
  public function generate_vk_logs_view_html($key, $data)
    {
      $field_key = $this->get_field_key( $key );
			$defaults  = array(
				'short_log' => '',
				'detail_log_path' => '',
				'error' => ''
      );

      $data = wp_parse_args( $data, $defaults );
      ob_start();
      ?>

        </table>
          <div id="<?php echo esc_attr( $field_key ); ?>">
            <div style="margin-top: 20px; margin-bottom: 20px">
								<button id="clear_vk_logs" class="button-secondary" type="button">
	                  <?php echo __('Clear', 'vkontakte'); ?>
	              </button>
                <a href="<?php echo $data['detail_log_path']; ?>" class="button-secondary" style="margin-left: 15px" download="">
                  <?php echo __('Details', 'vkontakte'); ?>
                </a>
            </div>

            <?php if ( empty( $data['error'] ) ) : ?>
            <div style="width: 100%;">
              <textarea wrap="off" rows="15" readonly
                        style="
	                        background-color: #eee;
	                        height: auto;
	                        width: 100%;
	                        padding: 8px 13px;
	                        font-size: 12px;
	                        line-height: 1.42857;
	                        color: #555;
	                        border: 1px solid #ccc;
													border-radius: 3px;"
							><?php echo $data['short_log']; ?></textarea>
            </div>
            <?php else : ?>
            <div style="color: red; margin-top: 10px">
                <?php echo $data['error']; ?>
            </div>
            <?php endif; ?>
          </div>
        <table class="form-table">

      <?php

      return ob_get_clean();
    }

  /**
   * Script show|hide rows categories
   */
  function show_categories()
  {
    ?>
			<script type="text/javascript">

				jQuery('span.hidden-category').hover().css({
					'cursor':'pointer'
				});

				jQuery('span.hidden-category').bind(
					'click',
					function() {
						let flag;

			      if(jQuery(this).hasClass('flag-show')) {
			        jQuery(this).removeClass('flag-show').addClass('flag-hidden');
							jQuery(this).html('&#11015;').css({'opacity': '1'});
			        flag = 'hidden';
						} else if (jQuery(this).hasClass('flag-hidden')) {
			        jQuery(this).removeClass('flag-hidden').addClass('flag-show');
			        jQuery(this).html('&#11014;').css({'opacity': '0.5'});
			        flag = 'show';
						}

            hidden_children(jQuery(this).parent('.row-category'), flag);
					}
				);

				function hidden_children(parent, flag) {
			    let id = jQuery(parent).attr('data-id');
					let children = jQuery('div.row-category[data-parent=' + id + ']');

					if (children) {
						jQuery(children).each(function(index, child) {
				      if(flag === 'show') {
								jQuery(child).show(100);
								jQuery(child).next('hr').show(100);
							} else if (flag === 'hidden') {
								jQuery(child).hide(100);
								jQuery(child).next('hr').hide(100);
							}

				      if (flag === 'hidden' || (flag === 'show' && jQuery(child).children('span.hidden-category').hasClass('flag-show'))) {
				        hidden_children(child, flag);
				      }
				    })
					}
				}

			</script>

		<?php
  }

  /**
   * Script show|hide block settings
   */
  function show_blocks()
  {
    ?>
			<script type="text/javascript">
				jQuery('h3.vkontakte_hidden').hover().css({
					'cursor':'pointer',
					'width':'310px'
				});
				jQuery('h3.vkontakte_hidden').bind(
					'click',
					function() {
						if(jQuery(this).next('table.form-table').is(":hidden")) {
							jQuery(this).next('table.form-table').show(100);
							jQuery(this).find('span').html('&#11014;');
						} else {
							jQuery(this).next('table.form-table').hide(100);
							jQuery(this).find('span').html('&#11015;');
						}
					}
				);
			</script>

		<?php
  }

  /**
	 * Relationship of category checkboxes
	 */
  function checked_checkbox_categories()
  {
    ?>

			<script type="text/javascript">
				jQuery("input.selection-of-categories:checkbox").on('change', function() {
			    let id = jQuery(this).attr('data-id');

	        if (jQuery(this).is(':checked')) {
		        jQuery('input.selection-of-categories[data-parent=' + id + ']').prop('checked', true).trigger('change');
			    } else {
		        jQuery('input.selection-of-categories[data-parent=' + id + ']').prop('checked', false).trigger('change');
			    }

	        checked_parent_categories(this, id);
			  });

				function checked_parent_categories(chexbox, last_chosen)
				{
				  let parent_id = jQuery(chexbox).attr('data-parent');

				  if (parent_id) {
					  let parent = jQuery('input.selection-of-categories[data-id=' + parent_id + ']');
				    let sisters = jQuery('input.selection-of-categories[data-parent=' + parent_id + ']');
				    let flag = true;

				    jQuery(sisters).each(function(index, child) {
				      if (!jQuery(child).is(':checked')) {
				        flag = false;
					    }
				    })

				    if (parent) {
			        if (flag && !jQuery(parent).is(':checked')) {
				        jQuery(parent).prop('checked', true);
					    } else if (!flag && jQuery(parent).is(':checked')) {
				        jQuery(parent).prop('checked', false);
					    }

			        checked_parent_categories(parent);
				    }
				  }
				}
			</script>

		<?php
  }

  /**
	 * Relationship of category selects
   */
  function checked_select_categories()
  {
	  ?>

		<script type="text/javascript">
			jQuery("select.matching-categories").on('change', function() {
		    let id = jQuery(this).attr('data-id');
		    let value = jQuery(this).val();

        jQuery('select.matching-categories[data-parent=' + id + ']').val(value).trigger('change');

			  let parent_id = jQuery(this).attr('data-parent');

			  if (parent_id) {
				  let parent = jQuery('select.matching-categories[data-id=' + parent_id + ']');
			    let sisters = jQuery('select.matching-categories[data-parent=' + parent_id + ']');
			    let flag = true;

			    jQuery(sisters).each(function(index, child) {
			      if (jQuery(child).val() !== value) {
			        flag = false;
				    }
			    })

			    if (parent && flag) {
            jQuery(parent).val(value);
			    }
			  }
			});
		</script>

		<?php
  }

  /**
	 * Counting the number of selected items
	 */
  function count_selected_products()
  {
    ?>

		<script type="text/javascript">
			count_selected_products();

			jQuery("input.selection-of-categories:checkbox").on('change', function() {
				count_selected_products();
		  });

			function count_selected_products()
			{
			  let all_checked = [];
			  let checked = [];
			  let parent_id;
			  let count = 0;

			  jQuery('input.selection-of-categories:checkbox:checked').each(function() {
					all_checked.push(jQuery(this).attr('data-id'));
				});

			  jQuery('input.selection-of-categories:checkbox:checked').each(function() {
			    parent_id = jQuery(this).attr('data-parent');

			    if (jQuery.inArray(parent_id, all_checked) < 0) {
					  checked.push(jQuery(this).attr('data-id'));
					}
				});

				jQuery(checked).each(function(index, id) {
		      count += +jQuery('span.count-category-' + id).html().replace(/[^+\d]/g, '');
		    })

		    jQuery('span.selected_products_categories_options').html('(' + count + ')');

				if (count > 14999) {
			    jQuery('span.error-limit-count').show();
				} else {
			    jQuery('span.error-limit-count').hide();
				}
			}
		</script>

		<?php
  }

  /**
   * Add button in admin
   */
  function add_vkontakte_button() {
        global $wp_admin_bar;
        if ( !is_super_admin() || !is_admin_bar_showing() || !is_admin())
            {return;}

        $wp_admin_bar->add_menu(
            array(
                'id' => 'vkontakte_top_menu',
                'title' => __('VKontakte', 'vkontakte')
            )
        );

        $wp_admin_bar->add_menu(
            array(
                'id' => 'vkontakte_ajax_generate_setings',
                'title' => __('Settings', 'vkontakte'),
                'href'=> get_site_url().'/wp-admin/admin.php?page=wc-settings&tab=integration&section=integration-vkontakte',
                'parent' => 'vkontakte_top_menu',
                'class' => 'vkontakte_ajax_settings'
            )
        );
    }


  /**
   * Helper methods
   */

  /**
	 * @param $settings
	 *
	 * @return bool
	 */
	public function check_options_oauth( $settings ) {
		if ( ! empty( $settings['id_application'] ) && ! empty( $settings['secret_key'] ) && ! empty( $settings['id_group'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieving settings, including those just saved
	 *
	 * @return array
	 */
	public function get_new_option() {
		$options = get_option( static::$option_key );

		foreach ( $this->get_post_data() as $key => $post_datum ) {
			if ( strripos( $key, $this->plugin_id . $this->id ) !== false ) {
				$options[ str_replace( $this->plugin_id . $this->id . '_', '', $key ) ] = $post_datum;
			}
		}

		return $options;
	}

		protected function clearOptions( $options ) {
			if ( ! empty( $options ) && is_array( $options ) ) {
				foreach ( $options as $key => $value ) {
					if ( ( is_int( $key ) && empty( $value ) ) || ( is_string( $value ) && ! strlen( $value ) ) ) {

						unset( $options[ $key ] );
					}
				}
			}

			return $options;
		}

		protected function trim_settings( &$settings ) {
			foreach ($settings as &$setting) {
				if (is_string($setting)) {
					$setting = trim($setting);
				} elseif (is_array($setting)) {
					$this->trim_settings($setting);
				}
			}
		}
}
