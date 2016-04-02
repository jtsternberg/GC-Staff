<?php

class GCST_Staff_Test extends WP_UnitTestCase {

	function test_sample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function test_class_exists() {
		$this->assertTrue( class_exists( 'GCST_Staff') );
	}

	function test_class_access() {
		$this->assertTrue( gc_staff()->gc-staff instanceof GCST_Staff );
	}

  function test_cpt_exists() {
    $this->assertTrue( post_type_exists( 'gc-staff' ) );
  }
}
