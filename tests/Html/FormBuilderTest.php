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
		$form1 = $this->formBuilder->open(array('method' => 'GET'));

		$this->assertEquals('<form method="GET" action="http://localhost/foo" accept-charset="UTF-8">', $form1);
	}
}
