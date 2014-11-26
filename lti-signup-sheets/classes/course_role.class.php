<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';


	class Course_Role extends Db_Linked {
		public static $fields = array('course_role_id', 'priority', 'course_role_name', 'flag_delete');
		public static $primaryKeyField = 'course_role_id';
		public static $dbTable = 'course_roles';
        public static $entity_type_label = 'course_role';

        # this matches hardcoded DB values
		public static $VALID_COURSE_ROLE_NAMES = ['teacher','student','observer','alumni'];
        public static $SORT_BY_COURSE_ROLE_NAMES = ['teacher'=>10,'student'=>20,'observer'=>30,'alumni'=>40];

		public static function cmp($a, $b) {
			# The most powerful system admin role is priority = 1; lowest anonymous/guest priority is X
			if ($a->priority == $b->priority) {
                if ($a->course_role_name == $b->course_role_name) {
    				return 0;
                }
                return (Course_Role::$SORT_BY_COURSE_ROLE_NAMES[$a->course_role_name] < Course_Role::$SORT_BY_COURSE_ROLE_NAMES[$b->course_role_name]) ? -1 : 1;
			}
			return ($a->priority < $b->priority) ? -1 : 1;
		}


    }