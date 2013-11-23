<?php
namespace Avh\Html;

/**
 * HTML helper class.
 * Provides generic methods for generating various HTML
 * tags and making output HTML safe.
 */
class HtmlBuilder
{

    /**
     *
     * @var array preferred order of attributes
     */
    // @formatter:off
    private $attribute_order = array(
                        'action',
                        'method',
                        'type',
                        'id',
                        'name',
                        'value',
                        'href',
                        'src',
                        'width',
                        'height',
                        'cols',
                        'rows',
                        'size',
                        'maxlength',
                        'rel',
                        'media',
                        'accept-charset',
                        'accept',
                        'tabindex',
                        'accesskey',
                        'alt',
                        'title',
                        'class',
                        'style',
                        'selected',
                        'checked',
                        'readonly',
                        'disabled'
                );
    // @formatter:on

    /**
     *
     * @var boolean automatically target external URLs to a new window?
     */
    public $windowed_urls = false;

    private $base_uri;

    /**
     * Create a new HTML builder instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Create HTML link anchors.
     * Note that the title is not escaped, to allow
     * HTML elements within links (images, etc).
     *
     * echo HtmlBuilder->anchor('/user/profile', 'My Profile');
     *
     * @param string $uri
     *            URL or URI string
     * @param string $title
     *            link text
     * @param array $attributes
     *            HTML anchor attributes
     * @return string
     * @uses HtmlBuilder->attributes
     */
    public function anchor($uri, $title = null, array $attributes = null)
    {
        if ($title === null) {
            // Use the URI as the title
            $title = $uri;
        }

        if ($uri === '') {
            // Only use the base URL
            $uri = home_url('/');
        } else {
            if (strpos($uri, '://') !== false) {
                if ($this->windowed_urls === true and empty($attributes['target'])) {
                    // Make the link open in a new window
                    $attributes['target'] = '_blank';
                }
            } elseif ($uri[0] !== '#') {
                // Make the URI absolute for non-id anchors
                $uri = plugin_dir_url($uri);
            }
        }

        // Add the sanitized link to the attributes
        $attributes['href'] = $uri;

        return '<a' . $this->attributes($attributes) . '>' . $title . '</a>';
    }

    /**
     * Creates an email (mailto:) anchor.
     * Note that the title is not escaped,
     * to allow HTML elements within links (images, etc).
     *
     * echo HtmlBuilder->mailto($address);
     *
     * @param string $email
     *            email address to send to
     * @param string $title
     *            link text
     * @param
     *            array %attributes HTML anchor attributes
     * @return string
     * @uses HtmlBuilder->attributes
     */
    public function mailto($email, $title = null, array $attributes = null)
    {
        if ($title === null) {
            // Use the email address as the title
            $title = $email;
        }

        $email = $this->obfuscate('mailto:') . $email;

        return '<a href="' . $email . '"' . $this->attributes($attributes) . '>' . esc_html($title) . '</a>';
    }

    /**
     * Creates a image link.
     *
     * echo HtmlBuilder->image('media/img/logo.png', array('alt' => 'My Company'));
     *
     * @param string $file
     *            file name
     * @param array $attributes
     *            default attributes
     * @return string
     * @uses URL::base
     * @uses HtmlBuilder->attributes
     */
    public function image($file, array $attributes = null)
    {
        // Add the image link
        $attributes['src'] = $file;

        return '<img' . $this->attributes($attributes) . ' />';
    }

    /**
     * Compiles an array of HTML attributes into an attribute string.
     * Attributes will be sorted using AVH2_Html::$attribute_order for consistency.
     *
     * echo '<div'.HtmlBuilder->attributes($attrs).'>'.$content.'</div>';
     *
     * @param array $attributes
     *            attribute list
     * @return string
     */
    public function attributes(array $attributes = null)
    {
        if (empty($attributes)) {
            return '';
        }

        $sorted = array();
        foreach ($this->attribute_order as $key) {
            if (isset($attributes[$key])) {
                // Add the attribute to the sorted list
                $sorted[$key] = $attributes[$key];
            }
        }

        // Combine the sorted attributes
        $attributes = $sorted + $attributes;

        $compiled = '';
        foreach ($attributes as $key => $value) {
            $element = $this->attributeElement($key, $value);
            if (!is_null($element)) {
                $compiled .= ' ' . $element;
            }
        }

        return $compiled;
    }

    /**
     * Build a single attribute element.
     *
     * @param string $key
     * @param string $value
     * @return string
     */
    protected function attributeElement($key, $value)
    {
        if (is_numeric($key)) {
            $key = $value;
        }
        ;

        if (!is_null($value)) {
            return $key . '="' . esc_attr($value) . '"';
        }
        ;
    }

    /**
     * Obfuscate a string to prevent spam-bots from sniffing it.
     *
     * @param string $value
     * @return string
     */
    public function obfuscate($value)
    {
        $safe = '';

        foreach (str_split($value) as $letter) {
            // To properly obfuscate the value, we will randomly convert each letter to
            // its entity or hexadecimal representation, keeping a bot from sniffing
            // the randomly obfuscated letters out of the string on the responses.
            switch (rand(1, 3)) {
                case 1:
                    $safe .= '&#' . ord($letter) . ';';
                    break;

                case 2:
                    $safe .= '&#x' . dechex(ord($letter)) . ';';
                    break;

                case 3:
                    $safe .= $letter;
            }
        }

        return $safe;
    }
}
