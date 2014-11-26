<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfSUS_Opening extends WMSUnitTestCaseDB {
		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testSUS_OpeningAtributesExist() {
			$this->assertEqual(count(SUS_Opening::$fields), 14);

			$this->assertTrue(in_array('opening_id', SUS_Opening::$fields));
			$this->assertTrue(in_array('created_at', SUS_Opening::$fields));
			$this->assertTrue(in_array('updated_at', SUS_Opening::$fields));
			$this->assertTrue(in_array('flag_deleted', SUS_Opening::$fields));
			$this->assertTrue(in_array('last_user_id', SUS_Opening::$fields));
			$this->assertTrue(in_array('sus_sheet_id', SUS_Opening::$fields));
			$this->assertTrue(in_array('opening_set_id', SUS_Opening::$fields));
			$this->assertTrue(in_array('name', SUS_Opening::$fields));
			$this->assertTrue(in_array('description', SUS_Opening::$fields));
			$this->assertTrue(in_array('max_signups', SUS_Opening::$fields));
			$this->assertTrue(in_array('admin_comment', SUS_Opening::$fields));
			$this->assertTrue(in_array('begin_datetime', SUS_Opening::$fields));
			$this->assertTrue(in_array('end_datetime', SUS_Opening::$fields));
			$this->assertTrue(in_array('location', SUS_Opening::$fields));
		}

		//// static methods

		public function testOfCmp() {
			$s1 = SUS_Opening::getOneFromDb(['opening_id' => 701], $this->DB);
			$s2 = SUS_Opening::getOneFromDb(['opening_id' => 702], $this->DB);

			$this->assertEqual(SUS_Opening::cmp($s1, $s2), -1);
			$this->assertEqual(SUS_Opening::cmp($s1, $s1), 0);
			$this->assertEqual(SUS_Opening::cmp($s2, $s1), 1);
		}

	}