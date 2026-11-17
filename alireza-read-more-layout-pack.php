<?php
/**
 * Plugin Name: Alireza Read More — Screenshot Layout Pack
 * Description: Adds three independent, scoped visual presets to the existing Alireza Advanced Read More Elementor widget, without replacing its render or toggle engine.
 * Version: 1.0.0
 * Requires Plugins: elementor
 * Requires PHP: 7.4
 * Text Domain: aardm-layout-pack
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class AARDM_Screenshot_Layout_Pack {
    private const VERSION = '1.0.0';
    private const STYLE_HANDLE = 'aardm-screenshot-presets';
    private const SECTION_ID = 'aardm_screenshot_presets_section';
    private const CONTROL_ID = 'aardm_screenshot_preset';

    public static function init() : void {
        add_action( 'elementor/loaded', array( __CLASS__, 'bootstrap' ) );
        add_action( 'plugins_loaded', array( __CLASS__, 'bootstrap_if_loaded' ), 20 );
    }

    public static function bootstrap_if_loaded() : void {
        if ( did_action( 'elementor/loaded' ) ) {
            self::bootstrap();
        }
    }

    public static function bootstrap() : void {
        static $initialized = false;
        if ( $initialized ) {
            return;
        }
        $initialized = true;
        // Elementor exposes this callback while building controls for each widget.
        add_action( 'elementor/element/after_section_end', array( __CLASS__, 'register_widget_control' ), 20, 3 );
        add_action( 'elementor/frontend/after_enqueue_styles', array( __CLASS__, 'enqueue_styles' ) );
        add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'enqueue_styles' ) );
    }

    public static function widget_names() : array {
        // Extend this list through the filter when the original get_name() differs.
        $names = array(
            'alireza_advanced_read_more',
            'alireza-advanced-read-more',
            'alireza_read_more',
            'alireza-read-more',
            'alireza_advanced_readmore',
            'alireza-advanced-readmore',
        );
        $filtered = apply_filters( 'aardm_layout_pack_widget_names', $names );
        if ( ! is_array( $filtered ) ) {
            return $names;
        }
        return array_values( array_filter( array_map( 'strval', $filtered ), 'strlen' ) );
    }

    public static function is_target( $element ) : bool {
        if ( ! ( $element instanceof \Elementor\Widget_Base ) ) {
            return false;
        }
        $name = (string) $element->get_name();
        if ( in_array( $name, self::widget_names(), true ) ) {
            return true;
        }
        // Narrow, namespaced fallback. Never add a control to generic Elementor widgets.
        $slug = strtolower( str_replace( '_', '-', $name ) );
        return strpos( $slug, 'alireza-' ) !== false
            && ( strpos( $slug, 'read-more' ) !== false || strpos( $slug, 'readmore' ) !== false );
    }

    public static function register_widget_control( $element, $section_id, $args ) : void {
        if ( ! self::is_target( $element ) ) {
            return;
        }
        // Re-entrant calls are possible while closing our new section; guard accordingly.
        $controls = $element->get_controls();
        if ( isset( $controls[ self::SECTION_ID ] ) || isset( $controls[ self::CONTROL_ID ] ) ) {
            return;
        }
        $element->start_controls_section(
            self::SECTION_ID,
            array(
                'label' => esc_html__( 'Screenshot Read More Layouts', 'aardm-layout-pack' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        $element->add_control(
            self::CONTROL_ID,
            array(
                'label'        => esc_html__( 'Design Layout', 'aardm-layout-pack' ),
                'type'         => \Elementor\Controls_Manager::SELECT,
                'options'      => array(
                    ''           => esc_html__( 'Existing design (unchanged)', 'aardm-layout-pack' ),
                    'dental'     => esc_html__( '01 — Soft Gray / Blue Button', 'aardm-layout-pack' ),
                    'beauty'     => esc_html__( '02 — Warm Cream / Outline Button', 'aardm-layout-pack' ),
                    'industrial' => esc_html__( '03 — White / Centered Outline Button', 'aardm-layout-pack' ),
                ),
                'default'      => '',
                'prefix_class' => 'aardm-skin-',
                'description'  => esc_html__( 'Visual styles only: SEO content and the existing read-more engine are not modified.', 'aardm-layout-pack' ),
            )
        );
        $element->end_controls_section();
    }

    public static function enqueue_styles() : void {
        $path = plugin_dir_path( __FILE__ ) . 'assets/layouts.css';
        $url  = plugins_url( 'assets/layouts.css', __FILE__ );
        $version = file_exists( $path ) ? (string) filemtime( $path ) : self::VERSION;
        wp_enqueue_style( self::STYLE_HANDLE, $url, array(), $version );
    }
}

AARDM_Screenshot_Layout_Pack::init();
