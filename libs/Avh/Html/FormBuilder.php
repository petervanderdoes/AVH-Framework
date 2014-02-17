<?php
namespace Avh\Html;

use Avh\Html\HtmlBuilder;

class FormBuilder
{

    // @var Use tables to create FormBuilder
    private $use_table = false;

    private $option_name;

    private $nonce;

    private $html;

    public function __construct(\Avh\Html\HtmlBuilder $html)
    {
        $this->html = $html;
    }

    /**
     * Generates an opening HTML form tag.
     *
     * // FormBuilder will submit back to the current page using POST
     * echo FormBuilder::open();
     *
     * // FormBuilder will submit to 'search' using GET
     * echo FormBuilder::open('search', array('method' => 'get'));
     *
     * // When "file" inputs are present, you must include the "enctype"
     * echo FormBuilder::open(null, array('enctype' => 'multipart/form-data'));
     *
     * @param mixed $action
     *            form action, defaults to the current request URI, or [Request] class to use
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses HtmlBuilder->attributes
     */
    public function open($action = null, $attributes = array())
    {
        if (!isset($attributes['method'])) {
            // Use POST method
            $attributes['method'] = 'post';
        }

        return '<form action="' . $action . '"' . $this->html->attributes($attributes) . '>';
    }

    /**
     * Creates the closing form tag.
     *
     * echo FormBuilder::close();
     *
     * @return string
     */
    public function close()
    {
        return '</form>';
    }

    public function openTable($attributes = array())
    {
        $this->use_table = true;
        $attributes = array_merge($attributes, array('class' => 'form-table'));
        return '<table' . $this->html->attributes($attributes) . '>';
    }

    public function closeTable()
    {
        $this->use_table = false;

        return '</table>';
    }

    /**
     * Create the nonce field.
     * Instead of using the standard WordPress function, we duplicate the function but using the methods of this class.
     * This will create a more standard looking HTML output.
     *
     * @param string $nonce
     * @param boolean $referer
     * @return string
     */
    public function fieldNonce($referer = true)
    {
        $nonce_field = $this->hidden('_wpnonce', wp_create_nonce($this->nonce));
        if ($referer) {
            $ref = $_SERVER['REQUEST_URI'];
            $nonce_field .= $this->hidden('_wp_http_referer', $ref);
        }

        return $nonce_field;
    }

    public function fieldSettings($action, $use_nonce = true)
    {
        $return = $this->hidden('action', $action);
        if ($use_nonce) {
            $return .= $this->fieldNonce();
        }

        return $return;
    }

    public function text($name, $value = null, $attributes = array())
    {
        $attributes['type'] = 'text';
        return $this->input($name, $value, $attributes);
    }

    public function checkboxes($name, $options, $attributes = array())
    {
        $attributes['type'] = 'checkbox';

        $output_field = '';
        foreach ($options as $value => $attr) {
            unset($attributes['checked']);
            if (isset($attr['checked']) && $attr['checked']) {
                $attributes['checked'] = 'checked';
            }
            if ($attr['value'] === false) {
                $attr['value'] = null;
            }
            $output_field .= $this->input(array($name => $value), $attr['value'], $attributes);
            $output_field .= $this->label($value, $attr['text']);
            $output_field .= '<br>';
        }
        $return = $this->outputField($output_field);

        return $return;
    }

    public function select($name, $options = array(), $selected = null, $attributes = array())
    {
        return $this->getSelect($name, $options, $selected, $attributes);
    }

    /**
     * Creates a hidden form input.
     *
     * echo FormBuilder::hidden('csrf', $token);
     *
     * @param string $name
     *            input name
     * @param string $value
     *            input value
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses $this->input
     */
    public function hidden($name, $value = null, $attributes = array(), $use_option_name = false)
    {
        $attributes['type'] = 'hidden';

        return $this->input($name, $value, $attributes, $use_option_name);
    }

    /**
     * Creates a button form input.
     * Note that the body of a button is NOT escaped,
     * to allow images and other HTML to be used.
     *
     * echo FormBuilder::button('save', 'Save Profile', array('type' => 'submit'));
     *
     * @param string $name
     *            input name
     * @param string $body
     *            input value
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses HtmlBuilder->attributes
     */
    public function button($name, $body, $attributes = array())
    {
        // Set the input name
        $attributes['name'] = $name;

        return '<button' . $this->html->attributes($attributes) . '>' . $body . '</button>';
    }

    /**
     * Creates a submit form input.
     *
     * echo FormBuilder::submit(null, 'Login');
     *
     * @param string $name
     *            input name
     * @param string $value
     *            input value
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses FormBuilder::input
     */
    public function submit($name, $value, $attributes = array())
    {
        $attributes['type'] = 'submit';

        return '<p class="submit">' . $this->input($name, $value, $attributes) . '</p>';
    }

    /**
     * Creates a password form input.
     *
     * echo FormBuilder::password('password');
     *
     * @param string $name
     *            input name
     * @param string $value
     *            input value
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses $this->input
     */
    public function password($name, $value = null, $attributes = array())
    {
        $attributes['type'] = 'password';

        return $this->input($name, $value, $attributes);
    }

    /**
     * Creates a file upload form input.
     * No input value can be specified.
     *
     * echo FormBuilder::file('image');
     *
     * @param string $name
     *            input name
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses $this->input
     */
    public function file($name, $attributes = array())
    {
        $attributes['type'] = 'file';

        return $this->input($name, null, $attributes);
    }

    /**
     * Creates a checkbox form input.
     *
     * echo FormBuilder::checkbox('remember_me', 1, (bool) $remember);
     *
     * @param string $name
     *            input name
     * @param string $value
     *            input value
     * @param boolean $checked
     *            checked status
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses $this->input
     */
    public function checkbox($name, $value = null, $checked = null, $attributes = array())
    {
        $attributes['type'] = 'checkbox';

        if ($checked === true) {
            // Make the checkbox active
            $attributes['checked'] = 'checked';
        }

        if ($value === false) {
            $value = null;
        }

        return  $this->input($name, $value, $attributes);
    }

    /**
     * Creates a radio form input.
     *
     * echo FormBuilder::radio('like_cats', 1, $cats);
     * echo FormBuilder::radio('like_cats', 0, ! $cats);
     *
     * @param string $name
     *            input name
     * @param string $value
     *            input value
     * @param boolean $checked
     *            checked status
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses $this->input
     */
    public function radio($name, $value = null, $checked = false, $attributes = array())
    {
        $attributes['type'] = 'radio';

        if ($checked === true) {
            // Make the radio active
            $attributes[] = 'checked';
        }

        return $this->input($name, $value, $attributes);
    }

    /**
     * Creates a textarea form input.
     *
     * echo FormBuilder::textarea('about', $about);
     *
     * @param string $name
     *            textarea name
     * @param string $body
     *            textarea body
     * @param array $attributes
     *            html attributes
     * @param boolean $double_encode
     *            encode existing HTML characters
     * @return string
     * @uses HtmlBuilder->attributes
     */
    public function textarea($name, $body = '', $attributes = array())
    {
        // Set the input name
        $attributes['name'] = $name;

        // Add default rows and cols attributes (required)
        $attributes += array('rows' => 10, 'cols' => 50);

        return '<textarea' . $this->html->attributes($attributes) . '>' . esc_textarea($body) . '</textarea>';
    }

    /**
     * Creates a image form input.
     *
     * echo FormBuilder::image(null, null, array('src' => 'media/img/login.png'));
     *
     * @param string $name
     *            input name
     * @param string $value
     *            input value
     * @param array $attributes
     *            html attributes
     * @param boolean $index
     *            add index file to URL?
     * @return string
     * @uses $this->input
     */
    public function image($name, $value, $attributes = array())
    {
        $attributes['type'] = 'image';

        return $this->input($name, $value, $attributes);
    }

    // ____________PRIVATE FUNCTIONS____________

    /**
     * Creates a form input.
     * If no type is specified, a "text" type input will
     * be returned.
     *
     * echo FormBuilder::input('username', $username);
     *
     * @param string|array $name
     *            input name
     * @param string $value
     *            input value
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses HtmlBuilder->attributes
     */
    private function input($name, $value = null, $attributes = array(), $use_option_name = true)
    {
        // Set the input name
        if (isset($this->option_name) && $use_option_name) {
            if (!is_array($name)) {
                $attributes['name'] = $this->option_name . '[' . $name . ']';
                $id = $name;
            } else {
                $attributes['name'] = $this->option_name . '[' . key($name) . ']' . '[' . current($name) . ']';
                $id = current($name);
            }
        } else {
            $attributes['name'] = $name;
            $id = $name;
        }

        // Set the input value
        $attributes['value'] = $value;

        if (!isset($attributes['type'])) {
            // Default type is text
            $attributes['type'] = 'text';
        }

        if (!isset($attributes['id'])) {
            $attributes['id'] = $id;
        }

        return '<input' . $this->html->attributes($attributes) . ' />';
    }

    /**
     * Creates a select form input.
     *
     * echo FormBuilder::select('country', $countries, $country);
     *
     * [!!] Support for multiple selected options was added in v3.0.7.
     *
     * @param string $name
     *            input name
     * @param array $options
     *            available options
     * @param mixed $selected
     *            selected option string, or an array of selected options
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses HtmlBuilder->attributes
     */
    private function getSelect($name, $options = array(), $selected = null, $attributes = array())
    {
        // Set the input name
        if (isset($this->option_name)) {
            $attributes['name'] = $this->option_name . '[' . $name . ']';
        } else {
            $attributes['name'] = $name;
        }

        if (is_array($selected)) {
            // This is a multi-select, god save us!
            $attributes[] = 'multiple';
        }

        if (!is_array($selected)) {
            if ($selected === null) {
                // Use an empty array
                $selected = array();
            } else {
                // Convert the selected options to an array
                $selected = array((string) $selected);
            }
        }

        if (empty($options)) {
            // There are no options
            $options = '';
        } else {
            foreach ($options as $value => $name) {
                if (is_array($name)) {
                    // Create a new optgroup
                    $group = array('label' => $value);

                    // Create a new list of options
                    $group_options = array();

                    foreach ($name as $group_value => $group_name) {
                        // Force value to be string
                        $group_value = (string) $group_value;

                        // Create a new attribute set for this option
                        $option = array('value' => $group_value);

                        if (in_array($group_value, $selected)) {
                            // This option is selected
                            $option[] = 'selected';
                        }

                        // Change the option to the HTML string
                        $group_options[] = '<option' . $this->html->attributes($option) . '>' . esc_html($name) . '</option>';
                    }

                    // Compile the options into a string
                    $group_options = implode('', $group_options);

                    $options[$value] = '<optgroup' . $this->html->attributes($group) . '>' . $group_options . '</optgroup>';
                } else {
                    // Force value to be string
                    $value = (string) $value;

                    // Create a new attribute set for this option
                    $option = array('value' => $value);

                    if (in_array($value, $selected)) {
                        // This option is selected
                        $option[] = 'selected';
                    }

                    // Change the option to the HTML string
                    $options[$value] = '<option' . $this->html->attributes($option) . '>' . esc_html($name) . '</option>';
                }
            }

            // Compile the options into a single string
            $options = implode('', $options);
        }

        return '<select' . $this->html->attributes($attributes) . '>' . $options . '</select>';
    }

    /**
     * Creates a form label.
     * Label text is not automatically translated.
     *
     * echo FormBuilder::label('username', 'Username');
     *
     * @param string $name
     * @param string $text
     * @param array $attributes
     * @return string
     * @uses HtmlBuilder->attributes
     */
    public function label($name, $text = null, $attributes = array())
    {
        if ($text === null) {
            // Use the input name as the text
            $text = ucwords(preg_replace('/[\W_]+/', ' ', $input));
        }

        // Set the label target
        $attributes['for'] = $name;

        return '<label' . $this->html->attributes($attributes) . '>' . $text . '</label>';
    }

    public function output($label, $field)
    {
        $output_return = $this->outputLabel($label);
        $output_return .= $this->outputField($field);

        return $output_return;
    }

    public function outputLabel($label)
    {
        if ($this->use_table) {
            return '<tr><th scope="row">' . $label . '</th>';
        } else {
            return $label;
        }
    }

    public function outputField($field)
    {
        if ($this->use_table) {
            return '<td>' . $field . '</td></tr>';
        } else {
            return $field;
        }
    }

    // __________________________________________
    // ____________Setter and Getters____________
    // __________________________________________
    /**
     *
     * @param field_type $option_name
     */
    public function setOptionName($option_name)
    {
        $this->option_name = $option_name;
    }

    public function getOptionName()
    {
        return $this->option_name;
    }

    public function setNonceAction($nonce)
    {
        $this->nonce = $this->option_name . '-' . $nonce;
    }

    public function getNonceAction($nonce)
    {
        return $this->option_name . '-' . $nonce;
    }
}
