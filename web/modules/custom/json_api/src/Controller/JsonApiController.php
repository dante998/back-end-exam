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


        $resource_data = [];

        foreach ($node->get('field_external_resources') as $resource_item) {

          $file_upload_entity = $resource_item->entity->get('field_file_upload')->entity;

          $url = $file_upload_entity ? $this->generateCustomUrl($file_upload_entity->getFileUri(), $file_upload_entity->getMimeType()) : '';

        $resource_data[] = [
            'title' => $resource_item->entity ? $resource_item->entity->getTitle() : '',
            'description' => $resource_item->entity ? $resource_item->entity->get('field_resource_description')->value : '',
            'url' => $url,
        ];
      }


        $data[] = [
          'courseName' => $node->getTitle(),
          'description' => $node->get('field_description')->value,
          'startDate' => date('d-m-Y', strtotime($node->get('field_start_date')->value)),
          'endDate' => date('d-m-Y', strtotime($node->get('field_end_date')->value)),
          'instructor' => $instructor_data,
          'subject' => $subject ? $subject->getName() : '',
          'level' => $level ? $level->getName() : '',
          'department' => $department ? $department->getName() : '',
          'resources' => $resource_data,
        ];
      }
    }
     // Return the final format of the data as JSON response.
     return new JsonResponse($data);
 }
  /**
   * Generate a custom URL based on the provided pattern.
   *
   * @param string $fileUri
   *   The file URI.
   * 
   * @param string $mimeType
   *   The file MIME type.
   *
   * @return string
   *   The generated URL.
   */
  private function generateCustomUrl($fileUri, $mimeType) {
    // Get the base URL of the Drupal site
    $base_url = \Drupal::request()->getSchemeAndHttpHost();

    // Extracting the file extension from the MIME type.
    $extension = explode('/', $mimeType)[1];

    // Constructing the custom URL pattern.
    return $base_url . '/' . pathinfo($fileUri, PATHINFO_FILENAME) . '.' . $extension;
  }
}


