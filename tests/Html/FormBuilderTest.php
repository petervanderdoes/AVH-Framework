<?php
use Avh\Html\FormBuilder;
use Avh\Html\HtmlBuilder;


class FormBuilderTest extends WP_UnitTestCase  {

	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		$this->htmlBuilder = new HtmlBuilder();
		$this->formBuilder =  new FormBuilder($this->htmlBuilder);
	}


	public function testOpeningForm()
	{
		$form1 = $this->formBuilder->open('http://localhost/foo', array('method' => 'GET'));

		$this->assertEquals('<form action="http://localhost/foo" method="GET">', $form1);
	}

	public function testClosingForm() {
	    $this->assertEquals('</form>', $this->formBuilder->close());
	}

	public function testOpenTable() {
	    $this->assertEquals('<table class="form-table">', $this->formBuilder->openTable());
	}

	public function testCloseTable() {
	    $this->assertEquals('</table>', $this->formBuilder->closeTable());
	}

	public function testText() {
	    $form1 = $this->formBuilder->text('Label text', 'label_name', 'value', array('maxlength' => '32'));

	    $this->assertEquals('<label for="label_name">Label text</label><input type="text" id="label_name" name="label_name" value="value" maxlength="32" />', $form1);
	}

}
