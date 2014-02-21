<?php
use Avh\Html\FormBuilder;
use Avh\Html\HtmlBuilder;
use Illuminate\Container\Container;

class FormBuilderTest extends WP_UnitTestCase
{

    /**
     *
     * @var FormBuilder
     */
    public $formBuilder;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $container = new Container();

        $this->formBuilder = $container->make('Avh\Html\FormBuilder');
        parent::setUp();
    }

    public function testFormOpen()
    {
        $form1 = $this->formBuilder->open('http://localhost/foo', array('method' => 'GET'));

        $this->assertEquals('<form action="http://localhost/foo" method="GET">', $form1);
    }

    public function testFormClose()
    {
        $this->assertEquals('</form>', $this->formBuilder->close());
    }

    public function testFormOpenTable()
    {
        $this->assertEquals('<table class="form-table">', $this->formBuilder->openTable());
    }

    public function testFormCloseTable()
    {
        $this->assertEquals('</table>', $this->formBuilder->closeTable());
    }

    public function testFormText()
    {
        $form1 = $this->formBuilder->text('label_name', 'value', array('maxlength' => '32'));

        $this->assertEquals('<input type="text" id="label_name" name="label_name" value="value" maxlength="32" />', $form1);
    }

    public function testFormOptionname()
    {
        $this->formBuilder->setOptionName('option');
        $this->assertEquals('option', $this->formBuilder->getOptionName());
    }

    public function testFormCheckboxes()
    {
        $array_cb1 = array('cb_1' => array('text' => 'First', 'value' => '1', 'checked' => '1'));
        $array_cb2 = array('cb_2' => array('text' => 'Second', 'value' => '1', 'checked' => '0'));
        $array_cb3 = array('cb_3' => array('text' => 'Third', 'value' => '0', 'checked' => '0'));
        $array_cb4 = array('cb_4' => array('text' => 'CB', 'value' => false, 'checked' => '0'));
        $array_cb5 = array('cb_5' => array('text' => 'CB', 'value' => false, 'checked' => true));

        $this->formBuilder->setOptionName('option');
        $form1 = $this->formBuilder->checkboxes('cb_name', $array_cb1);
        $form2 = $this->formBuilder->checkboxes('cb_name', $array_cb2);
        $form3 = $this->formBuilder->checkboxes('cb_name', $array_cb3);
        $form4 = $this->formBuilder->checkboxes('cb_name', $array_cb4);
        $form5 = $this->formBuilder->checkboxes('cb_name', $array_cb5);
        $form6 = $this->formBuilder->checkboxes('cb_name', $array_cb5, array('class' => 'class_1'));

        $expected1 = '<input type="checkbox" id="cb_1" name="option[cb_name][cb_1]" value="1" checked="checked" /><label for="cb_1">First</label><br>';
        $expected2 = '<input type="checkbox" id="cb_2" name="option[cb_name][cb_2]" value="1" /><label for="cb_2">Second</label><br>';
        $expected3 = '<input type="checkbox" id="cb_3" name="option[cb_name][cb_3]" value="0" /><label for="cb_3">Third</label><br>';
        $expected4 = '<input type="checkbox" id="cb_4" name="option[cb_name][cb_4]" /><label for="cb_4">CB</label><br>';
        $expected5 = '<input type="checkbox" id="cb_5" name="option[cb_name][cb_5]" checked="checked" /><label for="cb_5">CB</label><br>';
        $expected6 = '<input type="checkbox" id="cb_5" name="option[cb_name][cb_5]" class="class_1" checked="checked" /><label for="cb_5">CB</label><br>';

        $this->assertEquals($expected1, $form1);
        $this->assertEquals($expected2, $form2);
        $this->assertEquals($expected3, $form3);
        $this->assertEquals($expected4, $form4);
        $this->assertEquals($expected5, $form5);
        $this->assertEquals($expected6, $form6);
    }

    public function testFormSelect()
    {
        $months = array();
        foreach (range(1, 12) as $month) {
            $months[$month] = strftime('%B', mktime(0, 0, 0, $month, 1));
        }
        $form1 = $this->formBuilder->select('month', $months);
        $form2 = $this->formBuilder->select('month', $months, 1);
        $form3 = $this->formBuilder->select('month', $months, null, array('id' => 'foo'));

        $this->formBuilder->setOptionName('option');
        $form4 = $this->formBuilder->select('month', $months);

        $this->assertContains('<select name="month"><option value="1">January</option><option value="2">February</option>', $form1);
        $this->assertContains('<select name="month"><option value="1" selected="selected">January</option>', $form2);
        $this->assertContains('<select id="foo" name="month"><option value="1">January</option>', $form3);
        $this->assertContains('<select name="option[month]"><option value="1">January</option><option value="2">February</option>', $form4);
    }

    public function testFormSubmit()
    {
        $form1 = $this->formBuilder->submit('submit', 'foo');
        $form2 = $this->formBuilder->submit('submit', 'foo', array('class' => 'span2'));

        $this->assertEquals('<p class="submit"><input type="submit" id="submit" name="submit" value="foo" /></p>', $form1);
        $this->assertEquals('<p class="submit"><input type="submit" id="submit" name="submit" value="foo" class="span2" /></p>', $form2);
    }
}
