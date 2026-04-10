<?php

declare(strict_types=1);

namespace Drupal\ecms_multisite_search\EventSubscriber;

use Drupal\search_api_solr\Event\PostFieldMappingEvent;
use Drupal\search_api_solr\Event\PreQueryEvent;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Fixes Solr field mapping and index_id filter for solr_multisite_all indexes.
 *
 * SearchApiSolrBackend::getDatasourceConfig() only recognises
 * 'solr_document' and 'solr_multisite_document' plugin IDs. For any other
 * Solr datasource two problems occur:
 *
 * 1. formatSolrFieldNames() reads $config['id_field'] and
 *    $config['language_field'] from the empty array getDatasourceConfig()
 *    returns, producing PHP warnings and an empty search_api_id mapping that
 *    causes "The result does not contain the essential ID field" errors.
 *
 * 2. getTargetedIndexId() falls back to the index's own machine name, so the
 *    backend emits '+index_id:multisite_test_all' which matches nothing in
 *    Solr (the stored value is the target index name
 *    e.g. acquia_search_index).
 *
 * We subscribe to both PostFieldMappingEvent and PreQueryEvent to fix these.
 */
class MultisiteQuerySubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      SearchApiSolrEvents::POST_FIELD_MAPPING => 'onPostFieldMapping',
      SearchApiSolrEvents::PRE_QUERY => 'onPreQuery',
    ];
  }

  /**
   * Supplies the id_field and language_field mappings for solr_multisite_all.
   *
   * The getDatasourceConfig() returns an empty array for unknown plugin IDs,
   * leaving search_api_id and search_api_language unmapped. Read the values
   * directly from the datasource configuration and inject them here.
   *
   * @param \Drupal\search_api_solr\Event\PostFieldMappingEvent $event
   *   The post-field-mapping event.
   */
  public function onPostFieldMapping(PostFieldMappingEvent $event): void {
    $index = $event->getIndex();

    if (!$index->isValidDatasource('solr_multisite_all')) {
      return;
    }

    try {
      $config = $index->getDatasource('solr_multisite_all')->getConfiguration();
    }
    catch (\Exception $e) {
      return;
    }

    $id_field = $config['id_field'] ?? 'id';
    $language_field = $config['language_field'] ?? 'ss_search_api_language';

    $mapping = $event->getFieldMapping();
    $mapping['search_api_id'] = $id_field;
    $mapping['search_api_language'] = $language_field;
    $event->setFieldMapping($mapping);
  }

  /**
   * Replaces the index_id filter for solr_multisite_all datasource indexes.
   *
   * @param \Drupal\search_api_solr\Event\PreQueryEvent $event
   *   The pre-query event.
   */
  public function onPreQuery(PreQueryEvent $event): void {
    $index = $event->getSearchApiQuery()->getIndex();

    if (!$index->isValidDatasource('solr_multisite_all')) {
      return;
    }

    try {
      $config = $index->getDatasource('solr_multisite_all')->getConfiguration();
    }
    catch (\Exception $e) {
      return;
    }

    $target_index = $config['target_index_machine_name'] ?? '';
    if (!$target_index) {
      return;
    }

    $solarium_query = $event->getSolariumQuery();

    // Remove the index_filter added by the backend. It used the current
    // index's own machine name as the index_id value, which matches no
    // documents in Solr. Replace it with the target index machine name.
    $solarium_query->removeFilterQuery('index_filter');
    $solarium_query->createFilterQuery('index_filter')
      ->setQuery('+index_id:' . $solarium_query->getHelper()->escapeTerm($target_index));
  }

}
