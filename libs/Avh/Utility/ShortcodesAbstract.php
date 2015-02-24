<?php
namespace Avh\Utility;

/**
 * Class ShortcodesAbstract
 *
 * @package Avh\Utility
 */
abstract class ShortcodesAbstract
{
    private $shortcode_controller;
    private $shortcode_map;

    /**
     * Method that's always called for the shortcode
     *
     * @param  array  $atts
     * @param  string $content
     * @param  string $tag
     *
     * @return string
     */
    public function bootstrap($atts, $content, $tag)
    {
        $method = $this->shortcode_map[$tag];
        ob_start();
        $html = '';
        if (is_callable([$this->shortcode_controller, $method])) {
            $html = $this->shortcode_controller->$method($atts, $content, $tag);
            $html .= ob_get_clean();
        }

        return $html;
    }

    /**
     * Register a shortcode
     *
     * @param string $tag
     * @param string $class
     */
    public function register($tag, $class)
    {
        $this->shortcode_map[$tag] = $class;
        add_shortcode($tag, [$this, 'bootstrap']);
    }

    /**
     * @param mixed $shortcode_controller
     */
    public function setShortcodeController($shortcode_controller)
    {
        $this->shortcode_controller = $shortcode_controller;
    }
}
