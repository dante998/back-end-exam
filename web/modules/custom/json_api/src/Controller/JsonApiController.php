<?php

namespace Drupal\json_api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Json api controller class.
 */
class JsonApiController extends ControllerBase {

  public function renderApi() {

    $course_content_type = 'course';

    $nodes = Node::loadMultiple(NULL, $course_content_type);

    $data = [];


    foreach ($nodes as $node) {

      if ($node->getType() == $course_content_type) {

        // Load the referenced instructor node.
        $instructor_reference = $node->get('field_instructor')->entity;
        // Load the classification of the courses.
        $subject = $node->get('field_subject')->entity;
        $level = $node->get('field_level')->entity;
        $department = $node->get('field_department')->entity;
        // Load the external resources.
        $resource = $node->get('field_external_resources')->entity;


        $instructor_data = [
            'name' => $instructor_reference->getTitle(),
            'bio' => $instructor_reference->get('field_bio')->value,
            'contactInfo' => $instructor_reference->get('field_contact_information')->value,
          ];

        $resource_data = [
            'title' => $resource ? $resource->getTitle() : '',
            'description' => $resource ? $resource->get('field_resource_description')->value : '',

        ];


        $data[] = [
          'courseName' => $node->getTitle(),
          'description' => $node->get('field_description')->value,
          'startDate' => $node->get('field_start_date')->value,
          'endDate' => $node->get('field_end_date')->value,
          'instructor' => $instructor_data,
          'subject' => $subject ? $subject->getName() : '',
          'level' => $level ? $level->getName() : '',
          'department' => $department ? $department->getName() : '',
          'resources' => $resource_data ,
        ];
      }
    }
     return new JsonResponse($data);
 }
}
