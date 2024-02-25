<?php

/**
 * @file
 * Contains \Drupal\dartharth_facetbreadcrumbs\Plugin\Block\CustomBreadcrumb.
 */


namespace Drupal\dartharth_facetbreadcrumbs\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Core\Url;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\Core\Render\Markup;


/**
 * @Block(
 *	 id = "custom_facet_breadcrumb",
 *	 admin_label = @Translation("Breadcrumb Block"),
 *	 category = @Translation("Breadcrumb custom")
 * )
 */
class CustomBreadcrumb extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        $config = \Drupal::config('dartharth_facetbreadcrumbs.settings');
        $bread_types = $config->get('bread_types');

        $catalog_point = $config->get('catalog_point');
        $catalog_point_name = $config->get('catalog_point_name');

        $pretty_paths_coder = $config->get('pretty_paths_coder');
        $category_facet_alias = $config->get('category_facet_alias');
        $vendor_facet_alias = $config->get('vendor_facet_alias');

        $facet_base_url = $config->get('facet_base_url');
        $full_url = $facet_base_url;
        $url_components = parse_url($full_url);
        $facet_catalog_path = $url_components['path'] ?? '';

        $separator_value = $config->get('breadcrumbs_separator');
        $separator = Markup::create($separator_value);

        $current_path = \Drupal::service('path.current')->getPath();
        $path_args = explode('/', $current_path);

        $aliasManager = \Drupal::service('path_alias.manager');

        $display = FALSE;
        $position = 1;
        $breadcrumbs = [];
        $breadcrumbs[] = [
            'url' => '/',
            'name' => t('Главная'),
            'position' => $position,
        ];
        $position++;

        if ($path_args[1] == 'node') {
            $pid = $path_args[2];
            $node = \Drupal::entityTypeManager()->getStorage('node')->load($path_args[2]);
            if ($node->type->getString() == 'product') {
                $display = TRUE;

                if ($catalog_point) {
                    $position = 2;
                    $breadcrumbs[] = [
                        'url' => ''.$facet_catalog_path.'',
                        'name' => $catalog_point_name,
                        'position' => $position,
                    ];
                    $position++;
                }
                if (!$node->field_category->isEmpty()) {
                    if(count($node->field_category) > '1') {
                        foreach ($node->field_category as $category_item) {
                            $category_id = $category_item->target_id;
                            $category = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($category_id);
                            $category_title = $category->name->value;
                            $category_url = \Drupal::service('pathauto.alias_cleaner')->cleanString($category_title);
                            $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($category_id);
                            if (!empty($parent_r)){
                                $parent_r = reset($parent_r);
                            }else{
                                $parent_r = NULL;
                            }
                            if ($parent_r !== false && $category->parent->target_id !== "0"){
                                $category_parent_id = $category->parent->target_id;
                                $category_parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($category_parent_id);
                                if ($category_parent->parent->target_id !== "0"){
                                    $first_parent_id = $category_parent->parent->target_id;
                                    $first_parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($first_parent_id);
                                    $first_parent_title = $first_parent->name->value;
                                    $first_parent_url = \Drupal::service('pathauto.alias_cleaner')->cleanString($first_parent_title);

                                    if($bread_types = 'no_facets') {
                                        $first_parent_url = $aliasManager->getAliasByPath('/taxonomy/term/' . $first_parent->tid->value);
                                        $first_url = str_replace('/catalog', '/catalog', $first_parent_url);
                                    } elseif ($bread_types = 'has_facets'){
                                        if($pretty_paths_coder = 'taxonomy_term_name_id'){
                                            $first_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$first_parent_url.'-'.$first_parent_id;
                                        } elseif ($pretty_paths_coder = 'taxonomy_term_name') {
                                            $first_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$first_parent_url.'';
                                        } elseif ($pretty_paths_coder = 'taxonomy_term_id') {
                                            $first_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$first_parent_id.'';
                                        }
                                    }


                                    $breadcrumbs[] = [
                                        'url' => $first_url,
                                        'name' => $first_parent_title,
                                        'position' => $position,
                                    ];
                                    $position++;
                                }

                                $category_parent_title = $category_parent->name->value;
                                $category_parent_url = \Drupal::service('pathauto.alias_cleaner')->cleanString($category_parent_title);
                                if($bread_types = 'no_facets') {
                                    $parent_url = $aliasManager->getAliasByPath('/taxonomy/term/' . $category_parent->tid->value);
                                    $parent_url = str_replace('/catalog', '/catalog', $parent_url);
                                } elseif ($bread_types = 'has_facets'){
                                    if($pretty_paths_coder = 'taxonomy_term_name_id'){
                                        $parent_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$category_parent_url.'-'.$category_parent_id;
                                    } elseif ($pretty_paths_coder = 'taxonomy_term_name') {
                                        $parent_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$category_parent_url.'';
                                    } elseif ($pretty_paths_coder = 'taxonomy_term_id') {
                                        $parent_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$category_parent_id.'';
                                    }
                                }


                                $breadcrumbs[] = [
                                    'url' => $parent_url,
                                    'name' => $category_parent_title,
                                    'position' => $position,
                                ];
                                $position++;

                                $child_name_url = \Drupal::service('pathauto.alias_cleaner')->cleanString($category_title);

                                if($bread_types = 'no_facets') {
                                    $child_url = $aliasManager->getAliasByPath('/taxonomy/term/' . $category->tid->value);
                                    $child_url = str_replace('/catalog', '/catalog', $child_url);
                                } elseif ($bread_types = 'has_facets'){
                                    if($pretty_paths_coder = 'taxonomy_term_name_id'){
                                        $child_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$child_name_url.'-'.$category_id;
                                    } elseif ($pretty_paths_coder = 'taxonomy_term_name') {
                                        $child_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$child_name_url.'';
                                    } elseif ($pretty_paths_coder = 'taxonomy_term_id') {
                                        $child_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$category_id.'';
                                    }
                                }

                                $breadcrumbs[] = [
                                    'url' => $child_url,
                                    'name' => $category_title,
                                    'position' => $position,
                                ];
                                $position++;
                                break;
                            }
                        }
                    } else {
                        foreach ($node->field_category as $category_item) {
                            $category_id = $category_item->target_id;
                            $category = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($category_id);
                            $category_title = $category->name->value;
                            $category_url = \Drupal::service('pathauto.alias_cleaner')->cleanString($category_title);
                            if($bread_types = 'no_facets') {
                                $category_url = $aliasManager->getAliasByPath('/taxonomy/term/' . $category->tid->value);
                                $category_url = str_replace('/catalog', '/catalog', $category_url);
                            } elseif ($bread_types = 'has_facets'){
                                if($pretty_paths_coder == 'taxonomy_term_name_id'){
                                    $category_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$category_url.'-'.$category_id;
                                } elseif ($pretty_paths_coder == 'taxonomy_term_name') {
                                    $category_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$category_url.'';
                                } elseif ($pretty_paths_coder = 'taxonomy_term_id') {
                                    $category_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$category_id.'';
                                }
                            }
                            $parent_id = $category->parent->target_id;
                            if($parent_id !== '0'){
                                $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($parent_id);
                                $parent_title = $parent->name->value;
                                $parent_url = \Drupal::service('pathauto.alias_cleaner')->cleanString($parent_title);

                                if($bread_types = 'no_facets') {
                                    $parent_url = $aliasManager->getAliasByPath('/taxonomy/term/' . $parent->tid->value);
                                    $parent_url = str_replace('/catalog', '/catalog', $parent_url);
                                } elseif ($bread_types = 'has_facets'){
                                    if($pretty_paths_coder == 'taxonomy_term_name_id'){
                                        $parent_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$parent_url.'-'.$parent_id;
                                    } elseif ($pretty_paths_coder == 'taxonomy_term_name') {
                                        $parent_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$parent_url.'';
                                    } elseif ($pretty_paths_coder = 'taxonomy_term_id') {
                                        $parent_url = ''.$facet_catalog_path.'/'.$category_facet_alias.'/'.$parent_id.'';
                                    }
                                }

                                $breadcrumbs[] = [
                                    'url' => $parent_url,
                                    'name' => $parent_title,
                                    'position' => $position,
                                ];
                                $position++;
                            }
                            $breadcrumbs[] = [
                                'url' => $category_url,
                                'name' => $category_title,
                                'position' => $position,
                            ];
                            $position++;
                        }
                    }
                }
            }
            $breadcrumbs[]['name'] = $node->title->value;
        }

        if ($bread_types = 'has_facets'){
            $path_parts = explode('/', trim($current_path, '/'));
            $facet_path = str_replace('/', '', $facet_catalog_path);
            if ($path_args[1] == $facet_path) {

                if($path_args[2] == $category_facet_alias || $path_args[2] == $vendor_facet_alias){

                    $display = TRUE;
                    if ($catalog_point) {
                        $position = 2;
                        $breadcrumbs[] = [
                            'url' => ''.$facet_catalog_path.'',
                            'name' => $catalog_point_name,
                            'position' => $position,
                        ];
                        $position++;
                    }

                    $path_string = implode('/', $path_args);

                    if (str_contains($path_string, $category_facet_alias) && !str_contains($path_string, $vendor_facet_alias)) {
                        if($pretty_paths_coder == 'taxonomy_term_name_id'){
                            $key = array_search($category_facet_alias, $path_args);
                            if ($key !== false) {
                                $key++;
                                preg_match('/(?<=-)[0-9]+$/', $path_args[$key], $category_arg);
                                if (!empty($category_arg)) {
                                    $term = Term::load($category_arg[0]);
                                    if ($term) {
                                        $name = $term->getName();
                                        $breadcrumbs[] = [
                                            'name' => $name,
                                            'position' => $position,
                                        ];
                                        $position++;
                                    }
                                }
                            }
                        } elseif ($pretty_paths_coder == 'taxonomy_term_name') {
                            $categoryNameAlias = $path_parts[array_search($category_facet_alias, $path_parts) + 1] ?? null;
                            if ($categoryNameAlias) {
                                $query = \Drupal::database()->select('path_alias', 'pa');
                                $query->fields('pa', ['path', 'alias']);
                                $query->condition('pa.alias', '%' . \Drupal::database()->escapeLike($categoryNameAlias) . '%', 'LIKE');
                                $path_aliases = $query->execute()->fetchAll();

                                foreach ($path_aliases as $path_alias) {
                                    $alias_parts = explode('/', $path_alias->alias);
                                    $last_part = end($alias_parts);
                                    if (strpos($path_alias->alias, $categoryNameAlias) !== false && preg_match('/taxonomy\/term\/(\d+)/', $path_alias->path, $matches)) {
                                        $term_id = $matches[1];
                                        $term = Term::load($term_id);
                                        $name = $term->name->value;
                                    }
                                }

                                $breadcrumbs[] = [
                                    'name' => $name,
                                    'position' => $position,
                                ];
                                $position++;
                            }
                        } elseif ($pretty_paths_coder = 'taxonomy_term_id') {
                            if($path_parts[1] === $category_facet_alias){
                                $category = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($path_parts[2]);
                                $category_title = $category->name->value;

                                $breadcrumbs[] = [
                                    'name' => $category_title,
                                    'position' => $position,
                                ];
                                $position++;
                            }
                        }
                    } elseif (str_contains($path_string, $category_facet_alias) && str_contains($path_string, $vendor_facet_alias)) {
                        if($pretty_paths_coder == 'taxonomy_term_name_id'){
                            $path_parts = explode('/', trim($path_string, '/'));

                            function getTermDetailsFromPath($path_parts, $facet_alias) {
                                $key = array_search($facet_alias, $path_parts);
                                if ($key !== false) {
                                    $key++;
                                    preg_match('/(?<=-)[0-9]+$/', $path_parts[$key], $matches);
                                    if (!empty($matches)) {
                                        $term = Term::load($matches[0]);
                                        if ($term) {
                                            $name = $term->getName();
                                            $id = $term->id();
                                            $url = $term->toUrl()->toString();
                                            return ['id' => $id, 'name' => $name, 'url' => $url];
                                        }
                                    }
                                }
                                return null;
                            }

                            $categoryDetails = getTermDetailsFromPath($path_parts, $category_facet_alias);

                            $vendorDetails = getTermDetailsFromPath($path_parts, $vendor_facet_alias);

                            $cata_url = '/'.$facet_path.'/'.$category_facet_alias.''.$categoryDetails['url'].'-'.$categoryDetails['id'].'';

                            $breadcrumbs[] = [
                                'url' => $cata_url,
                                'name' => $categoryDetails['name'],
                                'position' => $position,
                            ];
                            $position++;

                            $breadcrumbs[] = [
                                'name' => $vendorDetails['name'],
                                'position' => $position,
                            ];
                            $position++;

                        } elseif ($pretty_paths_coder == 'taxonomy_term_name') {
                            $path_parts = explode('/', trim($path_string, '/'));

                            function getTermByAlias($alias) {
                                $query = \Drupal::database()->select('path_alias', 'pa');
                                $query->fields('pa', ['path']);
                                $query->condition('pa.alias', "%$alias", 'LIKE');
                                $path_alias = $query->execute()->fetchField();

                                if ($path_alias && preg_match('/taxonomy\/term\/(\d+)/', $path_alias, $matches)) {
                                    return Term::load($matches[1]);
                                }

                                return null;
                            }

                            $categoryAlias = $path_parts[array_search($category_facet_alias, $path_parts) + 1] ?? null;
                            $vendorAlias = $path_parts[array_search($vendor_facet_alias, $path_parts) + 1] ?? null;

                            $categoryTerm = $categoryAlias ? getTermByAlias($categoryAlias) : null;
                            $vendorTerm = $vendorAlias ? getTermByAlias($vendorAlias) : null;

                            $cata_url = '/'.$facet_path.'/'.$category_facet_alias.''.$categoryTerm->toUrl()->toString().'';

                            $breadcrumbs[] = [
                                'url' => $cata_url,
                                'name' => $categoryTerm->getName(),
                                'position' => $position,
                            ];
                            $position++;

                            $breadcrumbs[] = [
                                'name' => $vendorTerm->getName(),
                                'position' => $position,
                            ];
                            $position++;

                        } elseif ($pretty_paths_coder = 'taxonomy_term_id') {
                            if($path_parts[1] === $category_facet_alias && $path_parts[3] === $vendor_facet_alias){
                                $category = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($path_parts[2]);
                                $vendor = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($path_parts[4]);
                                $category_title = $category->name->value;
                                $vendor_title = $vendor->name->value;

                                $cata_url = '/'.$facet_path.'/'.$category_facet_alias.'/'.$path_parts[2].'';

                                $breadcrumbs[] = [
                                    'url' => $cata_url,
                                    'name' => $category_title,
                                    'position' => $position,
                                ];
                                $position++;

                                $breadcrumbs[] = [
                                    'name' => $vendor_title,
                                    'position' => $position,
                                ];
                                $position++;
                            }
                        }

                    } elseif (!str_contains($path_string, $category_facet_alias) && str_contains($path_string, $vendor_facet_alias)) {
                        if($pretty_paths_coder == 'taxonomy_term_name_id'){
                            $key = array_search($vendor_facet_alias, $path_args);
                            if ($key !== false) {
                                $key++;
                                preg_match('/(?<=-)[0-9]+$/', $path_args[$key], $category_arg);
                                if (!empty($category_arg)) {
                                    $term = Term::load($category_arg[0]);
                                    if ($term) {
                                        $name = $term->getName();
                                        $breadcrumbs[] = [
                                            'name' => $name,
                                            'position' => $position,
                                        ];
                                        $position++;
                                    }
                                }
                            }
                        } elseif ($pretty_paths_coder == 'taxonomy_term_name') {
                            $endorNameAlias = $path_parts[array_search($vendor_facet_alias, $path_parts) + 1] ?? null;
                            if ($endorNameAlias) {
                                $query = \Drupal::database()->select('path_alias', 'pa');
                                $query->fields('pa', ['path', 'alias']);
                                $query->condition('pa.alias', '%' . \Drupal::database()->escapeLike($endorNameAlias) . '%', 'LIKE');
                                $path_aliases = $query->execute()->fetchAll();

                                foreach ($path_aliases as $path_alias) {
                                    $alias_parts = explode('/', $path_alias->alias);
                                    $last_part = end($alias_parts);
                                    if (strpos($path_alias->alias, $endorNameAlias) !== false && preg_match('/taxonomy\/term\/(\d+)/', $path_alias->path, $matches)) {
                                        $term_id = $matches[1];
                                        $term = Term::load($term_id);
                                        $name = $term->name->value;
                                    }
                                }

                                $breadcrumbs[] = [
                                    'name' => $name,
                                    'position' => $position,
                                ];
                                $position++;
                            }
                        } elseif ($pretty_paths_coder = 'taxonomy_term_id') {
                            if($path_parts[1] === $vendor_facet_alias){
                                $vendor = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($path_parts[2]);
                                $vendor_title = $vendor->name->value;

                                $breadcrumbs[] = [
                                    'name' => $vendor_title,
                                    'position' => $position,
                                ];
                                $position++;
                            }
                        }
                    }
                }
            }
        }

        if ($path_args[1] == 'taxonomy' && $path_args[2] == 'term') {
            $display = TRUE;

            if ($catalog_point) {
                $position = 2;
                $breadcrumbs[] = [
                    'url' => ''.$facet_catalog_path.'',
                    'name' => $catalog_point_name,
                    'position' => $position,
                ];
                $position++;
            }

            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($path_args[3]);

            if ($term->parent->target_id !== '0') {
                $aliasManager = \Drupal::service('path_alias.manager');
                $parent_url = $aliasManager->getAliasByPath('/taxonomy/term/' . $term->parent->target_id);
                $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term->parent->target_id);
                $parent_title = $parent->name->value;

                $subcat_id = $parent->parent->target_id;
                if($subcat_id !== '0'){
                    $subcat = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($subcat_id);
                    $subcataliasManager = \Drupal::service('path_alias.manager');
                    $subcat_url = $subcataliasManager->getAliasByPath('/taxonomy/term/' . $subcat_id);
                    $subcat_title = $subcat->name->value;

                    $breadcrumbs[] = [
                        'url' => $subcat_url,
                        'name' => $subcat_title,
                        'position' => $position,
                    ];
                    $position++;
                }
                $breadcrumbs[] = [
                    'url' => $parent_url,
                    'name' => $parent_title,
                    'position' => $position,
                ];
            }
            $breadcrumbs[]['name'] = $term->name->value;
        }

        $block = [
            '#theme' => 'breadcrumbs',
            '#breadcrumbs' => $breadcrumbs,
            '#isset' => $display,
            '#separator' => $separator,
            '#cache' => [
                'max-age' => 0,
            ]
        ];

        return $block;
    }

}
