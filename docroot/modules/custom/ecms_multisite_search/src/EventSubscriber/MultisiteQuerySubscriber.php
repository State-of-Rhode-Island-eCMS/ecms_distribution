<?php

declare(strict_types=1);

namespace Drupal\ecms_multisite_search\EventSubscriber;

use Drupal\search_api_solr\Event\PreQueryEvent;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Fixes the Solr index_id filter for the solr_multisite_all datasource.
 *
 * SearchApiSolrBackend::getDatasourceConfig() only recognises
 * 'solr_document' and 'solr_multisite_document' plugin IDs. For any other
 * Solr datasource, getTargetedIndexId() falls back to the index's own machine
 * name instead of the configured target index. This produces a filter query
 * like '+index_id:multisite_test_all' which matches nothing in Solr.
 *
 * We intercept the PreQueryEvent and replace that filter with the correct
 * target index machine name from the datasource configuration.
 */
class MultisiteQuerySubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      SearchApiSolrEvents::PRE_QUERY => 'onPreQuery',
    ];
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
