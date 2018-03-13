<?php

/**
 * This doctrine event subscriber will join a user table to the client table
 * thereby freeing the user table from the OAuth2 contraints
 */
namespace ZF\OAuth2\Doctrine\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Zend\Config\Config;
use DoctrineModule\Persistence\ProvidesObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;

class DynamicMappingSubscriber implements
    EventSubscriber,
    ObjectManagerAwareInterface
{
    use ProvidesObjectManager;

    protected $config = array();
    protected $mapping = array();

    public function __construct(Config $config, Config $mapping)
    {
        $this->config = $config;
        $this->mapping = $mapping;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getMapping()
    {
        return $this->mapping;
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
        $this->setObjectManager($eventArgs->getObjectManager());

        // the $metadata is the whole mapping info for this class
        $metadata = $eventArgs->getClassMetadata();

        switch ($metadata->getName()) {
            case $this->getConfig()->user_entity->entity:
                $metadata->mapOneToMany(array(
                    'targetEntity' => $this->getConfig()->client_entity->entity,
                    'fieldName' => $this->getConfig()->client_entity->field,
                    'mappedBy' => $this->getConfig()->user_entity->field,
                ));

                $metadata->mapOneToMany(array(
                    'targetEntity' => $this->getConfig()->access_token_entity->entity,
                    'fieldName' => $this->getConfig()->access_token_entity->field,
                    'mappedBy' => $this->getConfig()->user_entity->field,
                ));

                $metadata->mapOneToMany(array(
                    'targetEntity' => $this->getConfig()->authorization_code_entity->entity,
                    'fieldName' => $this->getConfig()->authorization_code_entity->field,
                    'mappedBy' => $this->getConfig()->user_entity->field,
                ));

                $metadata->mapOneToMany(array(
                    'targetEntity' => $this->getConfig()->refresh_token_entity->entity,
                    'fieldName' => $this->getConfig()->refresh_token_entity->field,
                    'mappedBy' => $this->getConfig()->user_entity->field,
                ));
                break;

            case $this->getConfig()->client_entity->entity:
                // Add unique constriant for clientId based on column
                // See https://github.com/TomHAnderson/zf-oauth2-doctrine/issues/24
                $clientIdField = $this->getMapping()->Client->mapping->client_id->name;

                $clientIdColumn = $metadata->columnNames[$clientIdField];
                $indexName = $this->_generateIdentifierName(
                    array_merge(array($metadata->table['name']), ['clientId']), "uniq", $this->_getMaxIdentifierLength()
                );
                $metadata->table['uniqueConstraints'][$indexName]['columns'][] = $clientIdColumn;

                $joinMap = array(
                    'targetEntity' => $this->getConfig()->user_entity->entity,
                    'fieldName' => $this->getConfig()->user_entity->field,
                    'inversedBy' => $this->getConfig()->client_entity->field,
                );
                if (isset($this->getConfig()->client_entity->additional_mapping_data)) {
                    $joinMap = array_merge(
                        $joinMap,
                        $this->getConfig()->client_entity->additional_mapping_data->toArray()
                    );
                }
                $metadata->mapManyToOne($joinMap);
                break;

            case $this->getConfig()->access_token_entity->entity:
                $joinMap = array(
                    'targetEntity' => $this->getConfig()->user_entity->entity,
                    'fieldName' => $this->getConfig()->user_entity->field,
                    'inversedBy' => $this->getConfig()->access_token_entity->field,
                );
                if (isset($this->getConfig()->access_token_entity->additional_mapping_data)) {
                    $joinMap = array_merge(
                        $joinMap,
                        $this->getConfig()->access_token_entity->additional_mapping_data->toArray()
                    );
                }
                $metadata->mapManyToOne($joinMap);
                break;

            case $this->getConfig()['authorization_code_entity']['entity']:
                $joinMap = array(
                    'targetEntity' => $this->getConfig()->user_entity->entity,
                    'fieldName' => $this->getConfig()->user_entity->field,
                    'inversedBy' => $this->getConfig()->authorization_code_entity->field,
                );
                if (isset($this->getConfig()->authorization_code_entity->additional_mapping_data)) {
                    $joinMap = array_merge(
                        $joinMap,
                        $this->getConfig()->authorization_code_entity->additional_mapping_data->toArray()
                    );
                }
                $metadata->mapManyToOne($joinMap);
                break;

            case $this->getConfig()->refresh_token_entity->entity:
                $joinMap = array(
                    'targetEntity' => $this->getConfig()->user_entity->entity,
                    'fieldName' => $this->getConfig()->user_entity->field,
                    'inversedBy' => $this->getConfig()->refresh_token_entity->field,
                );
                if (isset($this->getConfig()->refresh_token_entity->additional_mapping_data)) {
                    $joinMap = array_merge(
                        $joinMap,
                        $this->getConfig()->refresh_token_entity->additional_mapping_data->toArray()
                    );
                }
                $metadata->mapManyToOne($joinMap);
                break;

            case $this->getConfig()->scope_entity->entity:
                // Add unique constriant for clientId based on column
                // See https://github.com/TomHAnderson/zf-oauth2-doctrine/issues/24
                $nameField = $this->getMapping()->Scope->mapping->scope->name;
                $nameColumn = $metadata->columnNames[$nameField];
                $indexName = $this->_generateIdentifierName(
                    array_merge(array($metadata->table['name']), ['scope']), "uniq", $this->_getMaxIdentifierLength()
                );
                $metadata->table['uniqueConstraints'][$indexName]['columns'][] = $nameColumn;
                break;

            default:
                break;
        }
    }

    // Copied from Doctrine DBAL\Schema\Table
    protected function _generateIdentifierName($columnNames, $prefix='', $maxSize=30)
    {
        $hash = implode("", array_map(function ($column) {
            return dechex(crc32($column));
        }, $columnNames));

        return substr(strtoupper($prefix . "_" . $hash), 0, $maxSize);
    }

    protected function _getMaxIdentifierLength()
    {
        return $this->getObjectManager()
            ->getConnection()
            ->getSchemaManager()
            ->getDatabasePlatform()
            ->getMaxIdentifierLength()
            ;
    }
}
