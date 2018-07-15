<?php

namespace Drupal\bic\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides an Bank Identifier Code field form element.
 *
 * Properties:
 * - #maxlength: Maximum number of characters of input allowed.
 * - #size: The size of the input element in characters.
 * - #autocomplete_route_name: A route to be used as callback URL by the
 *   autocomplete JavaScript library.
 * - #autocomplete_route_parameters: An array of parameters to be used in
 *   conjunction with the route name.
 *
 * Usage example:
 * @code
 * $form['bic'] = array(
 *   '#type' => 'bic',
 *   '#title' => $this->t('BIC'),
 *   '#default_value' => $node->bic,
 *   '#size' => 60,
 *   '#maxlength' => 32,
 * '#required' => TRUE,
 * );
 * @endcode
 *
 * @FormElement("bic")
 */
class bic extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#size' => 60,
      '#maxlength' => 11,
      '#autocomplete_route_name' => FALSE,
      '#process' => [
        [$class, 'processAutocomplete'],
        [$class, 'processAjaxForm'],
        [$class, 'processPattern'],
        [$class, 'processGroup'],
      ],
      '#element_validate' => [
        [$class, 'validateBic'],
      ],
      '#pre_render' => [
        [$class, 'preRenderBic'],
        [$class, 'preRenderGroup'],
      ],
      '#theme' => 'input__bic',
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input !== FALSE && $input !== NULL) {
      // This should be a string, but allow other scalars since they might be
      // valid input in programmatic form submissions.
      if (!is_scalar($input)) {
        $input = '';
      }
      return str_replace(["\r", "\n"], '', $input);
    }
    return NULL;
  }

  /**
   * Form element validation handler for #type 'bic'.
   *
   * Note that #maxlength and #required is validated by _form_validate() already.
   */
  public static function validateBic(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = trim($element['#value']);
    $form_state->setValueForElement($element, $value);

    if ($value !== '' && !\IsoCodes\SwiftBic::validate($value)) {
      $form_state->setError($element, t('The bank identifier code %bic is not valid.', ['%bic' => $value]));
    }
  }

  /**
   * Prepares a #type 'bic' render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderBic($element) {
    $element['#attributes']['type'] = 'bic';
    Element::setAttributes($element, ['id', 'name', 'value', 'size', 'maxlength', 'placeholder']);
    static::setAttributes($element, ['form-bic']);

    return $element;
  }

}
