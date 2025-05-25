<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://abfahad.me
 * @since      1.0.0
 *
 * @package    Ifaq
 * @subpackage Ifaq/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ifaq
 * @subpackage Ifaq/admin
 * @author     Md Abdullah Al Fahad <mabf.fahad@gmail.com>
 */
class Ifaq_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        add_action('admin_menu', [$this, 'add_admin_menu']);

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ifaq_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ifaq_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ifaq-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ifaq_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ifaq_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ifaq-admin.js', array( 'jquery' ), $this->version, false );
        wp_localize_script($this->plugin_name, 'ifaq_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'ifaq_nonce'    => wp_create_nonce('ifaq_nonce_action'),
        ]);

	}

    public function add_admin_menu() {
        add_menu_page(
            'Interactive FAQ',
            'Interactive FAQ',
            'manage_options',
            'interactive-faq',
            [$this, 'render_interactive_faq'],
            'dashicons-editor-help',
            20
        );

        // Add "Add New" submenu under "Interactive FAQ"
        add_submenu_page(
            'interactive-faq', // Parent slug (must match the menu slug above)
            'Add New FAQ',   // Page title
            'Add New FAQ',       // Submenu label
            'manage_options',
            'ifaq_add_new',  // Submenu slug
            [$this, 'render_ifaq_admin_add_new'] // Callback method
        );

        // Add "Add New" submenu under "Interactive FAQ"
        add_submenu_page(
            'interactive-faq', // Parent slug (must match the menu slug above)
            'Settings ',   // Page title
            'Settings',       // Submenu label
            'manage_options',
            'ifaq_settings',  // Submenu slug
            [$this, 'ifaq_settings'] // Callback method
        );
    }

    public function render_interactive_faq() {
        //admin partials
        require_once IFAQ_PLUGIN_DIR.'/admin/partials/ifaq-admin-display.php';
    }

    public function render_ifaq_admin_add_new() {
        require_once IFAQ_PLUGIN_DIR.'/admin/partials/ifaq-admin-add-new-form.php';
    }

    public function ifaq_settings() {
        require_once IFAQ_PLUGIN_DIR.'/admin/partials/ifaq-admin-settings.php';
    }

}
