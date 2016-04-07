<?php

namespace Drupal\adjustable_titles;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;

function get_node()
{
    $request = \Drupal::request();

    if ($request->attributes->has('node')) {
        if (\Drupal::request()->attributes->get(RouteObjectInterface::ROUTE_NAME) == 'node.view') {
            return $request->attributes->get('node');
        } else if (\Drupal::request()->attributes->get(RouteObjectInterface::ROUTE_NAME) == 'entity.node.revision') {
            $vid = \Drupal::request()->attributes->get('node_revision');

            return \Drupal::entityManager()->getStorage('node')->loadRevision($vid);
        }
    }
}
