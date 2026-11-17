<?php
/* Local mock test only — does not load WordPress or Elementor, nor test real-site integration. */
namespace Elementor {
    class Widget_Base {
        private $name;
        private $controls = array();
        public function __construct( $name ) { $this->name = $name; }
        public function get_name() { return $this->name; }
        public function get_controls() { return $this->controls; }
        public function start_controls_section( $key, $opts ) { $this->controls[ $key ] = $opts; }
        public function add_control( $key, $opts ) { $this->controls[ $key ] = $opts; }
        public function end_controls_section() {}
    }
    class Controls_Manager {
        const TAB_STYLE = 'style';
        const SELECT = 'select';
    }
}
namespace {
    define( 'ABSPATH', '/' );
    function add_action( ...$args ) {}
    function did_action( ...$args ) { return 1; }
    function apply_filters( $name, $value ) { return $value; }
    function esc_html__( $text, $domain ) { return $text; }
    require dirname( __DIR__ ) . '/alireza-read-more-layout-pack.php';
    $target = new \Elementor\Widget_Base( 'alireza-advanced-read-more' );
    \AARDM_Screenshot_Layout_Pack::register_widget_control( $target, 'section_existing', array() );
    \AARDM_Screenshot_Layout_Pack::register_widget_control( $target, 'section_existing', array() );
    $controls = $target->get_controls();
    assert( count( $controls ) === 2 );
    assert( $controls['aardm_screenshot_preset']['prefix_class'] === 'aardm-skin-' );
    assert( count( $controls['aardm_screenshot_preset']['options'] ) === 4 );
    assert( $controls['aardm_screenshot_preset']['default'] === '' );
    $other = new \Elementor\Widget_Base( 'heading' );
    \AARDM_Screenshot_Layout_Pack::register_widget_control( $other, 'section_existing', array() );
    assert( count( $other->get_controls() ) === 0 );
    echo "PASS: target only, exactly one control, classic default preserved\n";
}
