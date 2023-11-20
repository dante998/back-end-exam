<?php
/**
 *@file
 */

 namespace Drupal\student_enrollment\Controller;

 use Drupal\Core\Controller\ControllerBase;
 use Drupal\Core\Database\Database;



 class EnrollmentsController extends ControllerBase {

  /**
   * Gets and returns all students.
   *
   * @return array|null
   */
  protected function load() {
    try {

      $database = \Drupal::database();
      $select_query = $database->select('users_field_data', 'usr');


      $select_query->join('user__field_first_name', 'ufn', 'usr.uid = ufn.entity_id');
      $select_query->join('user__field_last_name', 'uln', 'usr.uid = uln.entity_id');
      $select_query->join('user__field_course_select', 'ucs', 'usr.uid = ucs.entity_id');
      $select_query->join('node_field_data', 'nfd', 'ucs.field_course_select_target_id = nfd.nid');


      $select_query->addField('usr', 'uid');
      $select_query->addField('usr', 'name');
      $select_query->addExpression('CONCAT(ufn.field_first_name_value, " ", uln.field_last_name_value)', 'full_name');
      $select_query->addField('ucs', 'field_course_select_target_id');
      $select_query->addField('nfd', 'title');


      $entries = $select_query->execute()->fetchAll(\PDO::FETCH_ASSOC);

      return $entries;
     }

    catch (\Exception $e) {
      \Drupal::messenger()->addStatus(
        t('Unable to access database at the moment.')
      );
      return NULL;
    }
  }

  /**
   *
   * @return array
   */
  public function report() {
    $content = [];

    $content['message'] = [
     '#markup' => t('Bellow is a list of students including their information and the name of the course they enrolled.'),
    ];

    $headers = [
     t('id'),
     t('Username'),
     t('Full Name',),
     t('Course id'),
     t('Course title')
   ];



   $entries = $this->load();

   $table_rows = [];
   foreach ($entries as $entry) {
       $table_rows[] = [
           'id' => $entry['uid'],
           'username' => $entry['name'],
           'full_name' => $entry['full_name'],
           'course_id' => $entry['field_course_select_target_id'],
           'course_title' => $entry['title'],
       ];
   }


   $content['table'] = [
     '#type' => 'table',
     '#header' => $headers,
     '#rows' => $table_rows,
     '#empty' => t('No entries available.'),
 ];


 $content['#cache']['max-range'] = 0;


 return $content;

  }
 }
