<?php

/**
 * This doctrine event subscriber will join a user table to the client table
 * thereby freeing the user table from the OAuth2 contraints
 */

namespace ZF\OAuth2\Doctrine\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class DynamicMappingSubscriber implements EventSubscriber
{
    protected $config = array();
    protected $mapping = array();

    public function __construct($config, $mapping)
    {
        $this->config = $config;
        $this->mapping = $mapping;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::loadClassMetadata,
        );
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        // the $metadata is the whole mapping info for this class
        $metadata = $eventArgs->getClassMetadata();

        switch ($metadata->getName()) {
            case $this->config['user_entity']['entity']:
                $metadata->mapOneToMany(array(
                    'targetEntity' => $this->config['client_entity']['entity'],
                    'fieldName' => $this->config['client_entity']['field'],
                    'mappedBy' => $this->config['user_entity']['field'],
                ));

                $metadata->mapOneToMany(array(
                    'targetEntity' => $this->config['access_token_entity']['entity'],
                    'fieldName' => $this->config['access_token_entity']['field'],
                    'mappedBy' => $this->config['user_entity']['field'],
                ));

                $metadata->mapOneToMany(array(
                    'targetEntity' => $this->config['authorization_code_entity']['entity'],
                    'fieldName' => $this->config['authorization_code_entity']['field'],
                    'mappedBy' => $this->config['user_entity']['field'],
                ));

                $metadata->mapOneToMany(array(
                    'targetEntity' => $this->config['refresh_token_entity']['entity'],
                    'fieldName' => $this->config['refresh_token_entity']['field'],
                    'mappedBy' => $this->config['user_entity']['field'],
                ));
                break;

            case $this->config['client_entity']['entity']:
                // Add unique constriant for clientId based on column
                // See https://github.com/TomHAnderson/zf-oauth2-doctrine/issues/24
                $clientIdField = $this->mapping['ZF\OAuth2\Doctrine\Mapper\Client']['mapping']['client_id']['name'];
                $clientIdColumn = $metadata->columnNames[$clientIdField];
                $metadata->table['uniqueConstraints']['idx_' . $clientIdColumn . '_unique']['columns'][] =
                    $clientIdColumn;

                $joinMap = array(
                    'targetEntity' => $this->config['user_entity']['entity'],
                    'fieldName' => $this->config['user_entity']['field'],
                    'inversedBy' => $this->config['client_entity']['field'],
                );
                if (isset($this->config['client_entity']['additional_mapping_data'])) {
                    $joinMap = array_merge(
                        $joinMap,
                        $this->config['client_entity']['additional_mapping_data']
                    );
                }
                $metadata->mapManyToOne($joinMap);
                break;

            case $this->config['access_token_entity']['entity']:
                $joinMap = array(
                    'targetEntity' => $this->config['user_entity']['entity'],
                    'fieldName' => $this->config['user_entity']['field'],
                    'inversedBy' => $this->config['access_token_entity']['field'],
                );
                if (isset($this->config['access_token_entity']['additional_mapping_data'])) {
                    $joinMap = array_merge(
                        $joinMap,
                        $this->config['access_token_entity']['additional_mapping_data']
                    );
                }
                $metadata->mapManyToOne($joinMap);
                break;

            case $this->config['authorization_code_entity']['entity']:
                $joinMap = array(
                    'targetEntity' => $this->config['user_entity']['entity'],
                    'fieldName' => $this->config['user_entity']['field'],
                    'inversedBy' => $this->config['authorization_code_entity']['field'],
                );
                if (isset($this->config['authorization_code_entity']['additional_mapping_data'])) {
                    $joinMap = array_merge(
                        $joinMap,
                        $this->config['authorization_code_entity']['additional_mapping_data']
                    );
                }
                $metadata->mapManyToOne($joinMap);
                break;

            case $this->config['refresh_token_entity']['entity']:
                $joinMap = array(
                    'targetEntity' => $this->config['user_entity']['entity'],
                    'fieldName' => $this->config['user_entity']['field'],
                    'inversedBy' => $this->config['refresh_token_entity']['field'],
                );
                if (isset($this->config['refresh_token_entity']['additional_mapping_data'])) {
                    $joinMap = array_merge(
                        $joinMap,
                        $this->config['refresh_token_entity']['additional_mapping_data']
                    );
                }
                $metadata->mapManyToOne($joinMap);
                break;

            default:
                break;
        }
    }
}
