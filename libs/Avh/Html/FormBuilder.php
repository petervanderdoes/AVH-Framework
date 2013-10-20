<?php
namespace Avh\Html;

use Avh\Html\HtmlBuilder;

class FormBuilder
{

    // @var Use tables to create FormBuilder
    private $use_table = false;

    private $option_name;

    private $nonce;

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
     * @uses HtmlBuilder::attributes
     */
    public function open($action = null, array $attributes = null)
    {
        if (!isset($attributes['method'])) {
            // Use POST method
            $attributes['method'] = 'post';
        }

        return '<form action="' . $action . '"' . HtmlBuilder::attributes($attributes) . '>';
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

    public function openTable()
    {
        $this->use_table = true;

        return "\n<table class='form-table'>\n";
    }

    public function closeTable()
    {
        $this->use_table = false;

        return "\n</table>\n";
    }

    /**
     * Create the nonce field.
     * Instead of using the standard WordPress function, we duplicate the function but using the methods of this class.
     * This will create a more standard looking HTML output.
     *
     * @param  string  $nonce
     * @param  boolean $referer
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

    public function fieldSettings($action, $nonce)
    {
        $return = $this->hidden('action', $action);
        $return .= $this->fieldNonce();

        return $return;
    }

    public function text($label, $description, $name, $value = null, array $attributes = null)
    {
        $_label = $this->label($name, $label);
        $_field = $this->input($name, $value, $attributes);

        return $this->output($_label, $_field);
    }

    public function checkboxes($label, $descripton, $name, array $options, array $attributes = null)
    {
        $_label = $this->label($name, $label);
        $_return = $this->outputLabel($_label);
        $_field = '';
        foreach ($options as $value => $attr) {
            $_checked = (isset($attr['checked']) ? $attr['checked'] : false);
            $_field .= $this->checkbox($value, true, $_checked, $attributes);
            $_field .= $this->label($value, $attr['text']);
            $_field .= '<br>';
        }
        $_return .= $this->outputField($_field);

        return $_return;
    }

    public function select($label, $description, $name, array $options = null, $selected = null, array $attributes = null)
    {
        $_label = $this->label($name, $label);
        $_field = $this->getSelect($name, $options, $selected, $attributes);

        return $this->output($_label, $_field);
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
    public function hidden($name, $value = null, array $attributes = null, $use_option_name = false)
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
     * @uses HtmlBuilder::attributes
     */
    public function button($name, $body, array $attributes = null)
    {
        // Set the input name
        $attributes['name'] = $name;

        return '<button' . HtmlBuilder::attributes($attributes) . '>' . $body . '</button>';
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
    public function submit($name, $value, array $attributes = null)
    {
        $attributes['type'] = 'submit';

        return '<p class="submit">' . $this->input($name, $value, $attributes) . '</p>';
    }

    // ____________PRIVATE FUNCTIONS____________

    /**
     * Creates a form input.
     * If no type is specified, a "text" type input will
     * be returned.
     *
     * echo FormBuilder::input('username', $username);
     *
     * @param string $name
     *            input name
     * @param string $value
     *            input value
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses HtmlBuilder::attributes
     */
    private function input($name, $value = null, array $attributes = null, $use_option_name = true)
    {
        // Set the input name
        if (isset($this->option_name) && $use_option_name) {
            $attributes['name'] = $this->option_name . '[' . $name . ']';
        } else {
            $attributes['name'] = $name;
        }

        // Set the input value
        $attributes['value'] = $value;

        if (!isset($attributes['type'])) {
            // Default type is text
            $attributes['type'] = 'text';
        }

        if (!isset($attributes['id'])) {
            $attributes['id'] = $name;
        }

        return '<input' . HtmlBuilder::attributes($attributes) . ' />';
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
    private function password($name, $value = null, array $attributes = null)
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
    private function file($name, array $attributes = null)
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
    private function checkbox($name, $value = null, $checked = false, array $attributes = null)
    {
        $attributes['type'] = 'checkbox';

        if ($checked === true) {
            // Make the checkbox active
            $attributes[] = 'checked';
        }

        return $this->input($name, $value, $attributes);
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
    private function radio($name, $value = null, $checked = false, array $attributes = null)
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
     * @uses HtmlBuilder::attributes
     */
    private function textarea($name, $body = '', array $attributes = null, $double_encode = true)
    {
        // Set the input name
        $attributes['name'] = $name;

        // Add default rows and cols attributes (required)
        $attributes += array('rows' => 10, 'cols' => 50);

        return '<textarea' . HtmlBuilder::attributes($attributes) . '>' . esc_textarea($body) . '</textarea>';
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
     * @uses HtmlBuilder::attributes
     */
    private function getSelect($name, array $options = null, $selected = null, array $attributes = null)
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
                    $_options = array();

                    foreach ($name as $_value => $_name) {
                        // Force value to be string
                        $_value = (string) $_value;

                        // Create a new attribute set for this option
                        $option = array('value' => $_value);

                        if (in_array($_value, $selected)) {
                            // This option is selected
                            $option[] = 'selected';
                        }

                        // Change the option to the HTML string
                        $_options[] = '<option' . HtmlBuilder::attributes($option) . '>' . esc_html($name) . '</option>';
                    }

                    // Compile the options into a string
                    $_options = "\n" . implode("\n", $_options) . "\n";

                    $options[$value] = '<optgroup' . HtmlBuilder::attributes($group) . '>' . $_options . '</optgroup>';
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
                    $options[$value] = '<option' . HtmlBuilder::attributes($option) . '>' . esc_html($name) . '</option>';
                }
            }

            // Compile the options into a single string
            $options = "\n" . implode("\n", $options) . "\n";
        }

        return '<select' . HtmlBuilder::attributes($attributes) . '>' . $options . '</select>';
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
    private function image($name, $value, array $attributes = null, $index = false)
    {
        if (!empty($attributes['src'])) {
            if (strpos($attributes['src'], '://') === false) {
                // @todo Add the base URL
                // $attributes['src'] = URL::base($index) . $attributes['src'];
            }
        }

        $attributes['type'] = 'image';

        return $this->input($name, $value, $attributes);
    }

    /**
     * Creates a form label.
     * Label text is not automatically translated.
     *
     * echo FormBuilder::label('username', 'Username');
     *
     * @param string $input
     *            target input
     * @param string $text
     *            label text
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses HtmlBuilder::attributes
     */
    private function label($input, $text = null, array $attributes = null)
    {
        if ($text === null) {
            // Use the input name as the text
            $text = ucwords(preg_replace('/[\W_]+/', ' ', $input));
        }

        // Set the label target
        $attributes['for'] = $input;

        return '<label' . HtmlBuilder::attributes($attributes) . '>' . $text . '</label>';
    }

    private function output($label, $field)
    {
        $_return = $this->outputLabel($label);
        $_return .= $this->outputField($field);

        return $_return;
    }

    private function outputLabel($label)
    {
        if ($this->use_table) {
            return "\n<tr>\n\t<th scope='row'>" . $label . "</th>";
        } else {
            return "\n" . $label;
        }
    }

    private function outputField($field)
    {
        if ($this->use_table) {
            return "\n\t<td>\n\t\t" . $field . "\n\t</td>";
        } else {
            return "\n" . $field;
        }
    }

    // __________________________________________
    // ____________Setter and Getters____________
    // __________________________________________
    /**
     *
     * @param field_type $option_name
     */
    public function setOptionName($_option_name)
    {
        $this->option_name = $_option_name;
    }

    public function getOptionName()
    {
        return $this->option_name;
    }

    public function setNonceAction($_nonce)
    {
        $this->nonce = $this->option_name . '-' . $_nonce;
    }

    public function getNonceAction()
    {
        return $this->nonce;
    }
}
