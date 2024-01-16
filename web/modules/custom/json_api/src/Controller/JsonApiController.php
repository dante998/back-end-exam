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

        $instructor_data = [
            'name' => $instructor_reference->getTitle(),
            'bio' => $instructor_reference->get('field_bio')->value,
            'contactInfo' => $instructor_reference->get('field_contact_information')->value,
          ];

        $data[] = [
          'courseName' => $node->getTitle(),
          'description' => $node->get('field_description')->value,
          'startDate' => $node->get('field_start_date')->value,
          'endDate' => $node->get('field_end_date')->value,
          'instructor' => $instructor_data,
        ];
      }
    }

  return new JsonResponse($data);
 }
}
