<?php

namespace Drupal\dartharth_facetbreadcrumbs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Vocabulary;

class BreadcrumbsCustomSettingsForm extends ConfigFormBase {

    public function getFormId() {
        return 'dartharth_facetbreadcrumbs_settings_form';
    }

    protected function getEditableConfigNames() {
        return [
            'dartharth_facetbreadcrumbs.settings',
        ];
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('dartharth_facetbreadcrumbs.settings');

        $form['bread_types'] = [
            '#type' => 'radios',
            '#title' => $this->t('Choose whether your facets website has'),
            '#default_value' => $config->get('bread_types') ?: 'no_facets',
            '#options' => [
                'has_facets' => $this->t('Website have facets'),
                'no_facets' => $this->t('Website without facets'),
            ],
            '#description' => $this->t('If the site does not have facets, you can not fill in any field anymore. '),
        ];

        $form['facet_base_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Base URL'),
            '#default_value' => $config->get('facet_base_url'),
            '#description' => $this->t('Enter the base URL for the facet pages. For example: https://mywebsite.com/catalog or https://mywebsite.com/blog'),
            //'#required' => TRUE,
        ];

        $form['catalog_point'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Catalog point'),
            '#default_value' => $config->get('catalog_point'),
            '#description' => $this->t('Add link with your Catalog to the breadcrumbs?'),
        ];

        $form['catalog_point_name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Catalog point name'),
            '#default_value' => $config->get('catalog_point_name'),
            '#description' => $this->t('If you choice show catalog point - enter the catalog point name, for example - Catalog, Blog ...'),
            //'#required' => TRUE,
        ];

        $form['pretty_paths_coder'] = [
            '#type' => 'radios',
            '#title' => $this->t('Select Pretty Paths Coder Type'),
            '#default_value' => $config->get('pretty_paths_coder') ?: 'taxonomy_term_name_id',
            '#options' => [
                'taxonomy_term_name_id' => $this->t('Taxonomy term name + id'),
                'taxonomy_term_name' => $this->t('Taxonomy term name'),
                'taxonomy_term_id' => $this->t('Default (alias/id)'),
            ],
            '#description' => $this->t('Choose how the Pretty Paths should be coded.'),
        ];

        $form['category_facet_alias'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Category facet alias URL'),
            '#default_value' => $config->get('category_facet_alias'),
            '#description' => $this->t('Enter the facet alias from your category facet settings For example: category'),
            //'#required' => TRUE,
        ];

        $form['vendor_facet_alias'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Vendor facet alias URL'),
            '#default_value' => $config->get('vendor_facet_alias'),
            '#description' => $this->t('Enter the facet alias from your vendor facet settings For example: brand'),
            //'#required' => TRUE,
        ];

        $form['breadcrumbs_separator'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Separator for breadcrumbs'),
            '#default_value' => $config->get('breadcrumbs_separator'),
            '#description' => $this->t('Enter separator for breadcrumbs. For example, "/", "-", or SVG-code.'),
            '#rows' => 5,
        ];



        return parent::buildForm($form, $form_state);
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config('dartharth_facetbreadcrumbs.settings');

        $config->set('bread_types', $form_state->getValue('bread_types'));

        $config->set('pretty_paths_coder', $form_state->getValue('pretty_paths_coder'));

        $config->set('facet_base_url', $form_state->getValue('facet_base_url'));

        $config->set('category_facet_alias', $form_state->getValue('category_facet_alias'));

        $config->set('vendor_facet_alias', $form_state->getValue('vendor_facet_alias'));

        $config->set('catalog_point', $form_state->getValue('catalog_point'));

        $config->set('catalog_point_name', $form_state->getValue('catalog_point_name'));

        $config->set('breadcrumbs_separator', $form_state->getValue('breadcrumbs_separator'));

        $config->save();

        parent::submitForm($form, $form_state);
    }

}
