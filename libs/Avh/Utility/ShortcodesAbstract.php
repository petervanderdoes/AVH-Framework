<?php
namespace Avh\Utility;

abstract class ShortcodesAbstract
{

    private $shortcode_map;

    /**
     * Method that's always called for the shortcode
     *
     * @param  array  $atts
     * @param  string $content
     * @param  string $tag
     * @return string
     */
    public function bootstrap($atts, $content, $tag)
    {
        $method = $this->shortcode_map[$tag];
        ob_start();
        $html = '';
        if (is_callable(array($this, $method))) {
            $html = $this->$method($atts, $content, $tag);
            $html .= ob_get_clean();
        }

        return $html;
    }

    public function register($tag, $class)
    {
        $this->shortcode_map[$tag] = $class;
        add_shortcode($tag, array($this, 'bootstrap'));
    }
}
