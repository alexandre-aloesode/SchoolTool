<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

$route['default_controller'] = 'main';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/* CLASS */
$route['class'] = 'Classe/Class';

/* UNIT */
$route['unit'] = 'Unit/Unit';
$route['unit/job'] = 'Unit/UnitJob';
$route['unit/duplicate'] = 'Unit/UnitDuplicate';
$route['unit/end'] = 'Unit/UnitEnd';

/* PROMOTION */
$route['promotion'] = 'Promotion/Promotion';
$route['promotion/end'] = 'Promotion/PromotionEnd';
$route['promotion/faithfulness'] = 'Promotion/PromotionFaithfulness';

/* SECTION */
$route['section'] = 'Section/Section';

/* SKILL */
$route['skill'] = 'Skill/Skill';

/* JOB */
$route['job'] = 'Job/Job';
$route['student/job/available'] = 'Job/StudentJobAvailable';
$route['job/student/available'] = 'Job/JobStudentAvailable';

/* JOB_SKILL */
$route['job/skill'] = 'Job_Skill/JobSkill';

/* REGISTRATION */
$route['registration'] = 'Registration/Registration';
$route['job/student'] = 'Registration/JobStudent';
$route['job/await'] = 'Registration/getJobAwait';
$route['job/progress'] = 'Registration/getJobProgress';
$route['job/ready'] = 'Registration/getJobReady';
$route['job/checked'] = 'Registration/getJobChecked';
$route['job/done'] = 'Registration/getJobDone';
$route['group'] = 'Registration/Group';
$route['member'] = 'Registration/Member';
$route['group/review'] = 'Registration/GroupReview';
$route['group/validity'] = 'Registration/GroupValidity';
$route['group/available'] = 'Registration/GroupAvailable';
$route['group/click'] = 'Registration/GroupClick';
$route['job/corrector'] = 'Registration/JobCorrector';
$route['job/review'] = 'Registration/JobReview';
$route['unit/review'] = 'Registration/UnitReview';

/* STUDENT */
$route['student'] = 'Student/Student';
$route['student/activity'] = 'Student/StudentActivity';
$route['student/inactive'] = 'Student/StudentInactive';
$route['student/attendance'] = 'Student/StudentAttendance';
$route['group/attendance'] = 'Student/GroupAttendance';
$route['student/last_log'] = 'Student/LastLog';

/* UNIT_VIEWER */
$route['unit/student'] = 'Unit_Viewer/UnitStudent';
$route['unit/students'] = 'Unit_Viewer/UnitStudents';
$route['unit/students/current'] = 'Unit_Viewer/UnitStudentsCurrent';
$route['units/completed/students'] = 'Unit_Viewer/UnitsCompletedStudents';
$route['student/unit'] = 'Unit_Viewer/StudentUnit';

/* UNIT_GOALS */
$route['unit/goal'] = 'Unit_Goal/UnitGoal';
$route['unit/goal/student'] = 'Unit_Goal/UnitGoalStudent';

/* UNIT_COMPLETED */
$route['unit/completed'] = 'Unit_Completed/UnitCompleted';
$route['unit/revalidate'] = 'Unit_Completed/UnitRevalidate';

/* ACQUIERED_SKILL */
$route['student/skill'] = 'Acquiered_Skill/StudentSkill';
$route['student/skill/total'] = 'Acquiered_Skill/StudentSkillTotal';
$route['student/class/total'] = 'Acquiered_Skill/StudentClassTotal';
$route['student/promotion/class/total'] = 'Acquiered_Skill/StudentPromotionClassTotal';
$route['promotion/class/total'] = 'Acquiered_Skill/PromotionClassTotal';

/* WAITING_LIST */
$route['waitinglist'] = 'Waiting_List/WaitingList';
$route['job/waitinglist'] = 'Waiting_List/JobWaitingList';
$route['unit/waitinglist'] = 'Waiting_List/UnitWaitingList';

/* APPLICANT */
$route['applicant'] = 'Applicant/Applicant';
$route['applicant/status'] = 'Applicant/ApplicantStatus';
$route['applicant/situations'] = 'Applicant/Situations';
$route['applicant/studies'] = 'Applicant/Studies';

/* FOLLOWUP */
$route['followup'] = 'Followup/Followup';
$route['student/followup'] = 'Followup/StudentFollowup';

/* PROMOTION HISTORY */
$route['promotion/history'] = 'Promotion_History/PromotionHistory';

/* PROMOTION_UNIT */
$route['promotion/unit'] = 'Promotion_Unit/PromotionUnit';

/* UNIT HISTORY */
$route['unit/history'] = 'Unit_History/UnitHistory';

/* LOGTIME */
$route['logtime'] = 'Logtime/Logtime';
$route['logtime/real'] = 'Logtime/RealLogtime';
$route['student/last/logtime'] = 'Logtime/StudentLastLogtime';
$route['promotion/last/logtime'] = 'Logtime/PromotionLastLogtime';
$route['promotion/logtime/average'] = 'Logtime/PromotionLogtimeAverage';
$route['promotion/logtime'] = 'Logtime/PromotionLogtime';

/* ABSENCE */
$route['absence'] = 'Absence/Absence';

/* Logtime Event */
$route['logtime_event'] = 'Logtime_Event/Logtime_Event';

/* Calendar */
$route['calendar'] = 'Calendar/Calendar';

/* CalendarHistory */
$route['calendar/history'] = 'Calendar_History/CalendarHistory';

/* Calendar_Day */
$route['calendar_day'] = 'Calendar_Day/CalendarDay';

/* Alert */
$route['alert'] = 'Alert/Alert'; 

/* Log */
$route['log'] = 'Log/Log';

/* Upload new students */
$route['new_applicants'] = 'Applicant/NewApplicants';

/* Add new student */
$route['new_applicant'] = 'Applicant/NewApplicant';

/* Upload new badges */
$route['new_badges'] = 'Student/NewBadges';

/* Alternance */
$route['alternance'] = 'Alternance/Alternance';

/* Activity */
$route['activity'] = 'Activity/Activity';

/* Activity_Attendance */
$route['activity_attendance'] = 'Activity_Attendance/ActivityAttendance';

/* Invoice */
$route['invoice'] = 'Invoice/Invoice';
$route['invoice/count'] = 'Invoice/InvoiceCount';