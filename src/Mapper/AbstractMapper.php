<?php

namespace ZF\OAuth2\Doctrine\Mapper;

use Doctrine\ORM\QueryBuilder;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager as ProvidesObjectManagerTrait;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Config\Config;
use DateTime;
use Exception;

class AbstractMapper implements
    ObjectManagerAwareInterface
{
    use ProvidesObjectManagerTrait;

    /**
     * @var ZF\OAuth2\Doctrine\Mapper\MapperManager
     */
    protected $mapperManager;

    /**
     * Specific config for the current mapper
     *
     * @var array
     */
    protected $config;

    /**
     * @var data
     */
    protected $oAuth2Data = array();

    /**
     * @var data
     */
    protected $doctrineData = array();

    public function setMapperManager(MapperManager $mapperManager)
    {
        $this->mapperManager = $mapperManager;

        return $this;
    }

    public function getMapperManager()
    {
        return $this->mapperManager;
    }

    protected function getOAuth2Data()
    {
        return $this->oAuth2Data;
    }

    protected function setOAuth2Data(array $data)
    {
        $this->oAuth2Data = $data;

        return $this;
    }

    protected function getDoctrineData()
    {
        return $this->doctrineData;
    }

    protected function setDoctrineData(array $data)
    {
        $this->doctrineData = $data;

        return $this;
    }

    /**
     * Set the mapping config
     *
     * @param  array
     * @return this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Return the current config
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Pass data formatted for the oauth2 server
     * and populate both oauth2 and doctrine data
     */
    public function exchangeOAuth2Array(array $array)
    {
        $oAuth2Data = $this->getOAuth2Data();
        $doctrineData = $this->getDoctrineData();
        $config = $this->getConfig();

        foreach ($array as $key => $value) {
            if (!isset($this->getConfig()->mapping->$key)) {
                continue;
            }

            switch ($this->getConfig()->mapping->$key->type) {
                // Set the value in data
                case 'field':
                    switch ($this->getConfig()->mapping->$key->datatype) {
                        case 'datetime':
                            // Dates coming from OAuth2 are timestamps
                            $oAuth2Data[$key] = $value;
                            $date = new DateTime();
                            $date->setTimestamp($value);
                            $doctrineData[$this->getConfig()->mapping->$key->name] = $date;
                            break;
                        case 'boolean':
                            $oAuth2Data[$key] = (int) (bool) $value;
                            $doctrineData[$this->getConfig()->mapping->$key->name] = (bool) $value;
                            break;
                        default:
                            $oAuth2Data[$key] = $value;
                            $doctrineData[$this->getConfig()->mapping->$key->name] = $value;
                            break;
                    }
                    break;
                case 'collection':
                    $oAuth2String = array();
                    $fieldValues = explode(' ', $value);
                    /** @var QueryBuilder $queryBuilder */
                    $queryBuilder = $this->getObjectManager()->createQueryBuilder();
                    $queryBuilder->select('row')
                        ->from($this->getConfig()->mapping->$key->entity, 'row');

                    $queryBuilder->andwhere(
                        $queryBuilder->expr()->in(
                            'row.'
                            . $this->getConfig()->mapping->$key->name,
                            $fieldValues
                        )
                    );

                    $oAuth2Data[$key] = $value;
                    $doctrineData[$this->getConfig()->mapping->$key->name] = $queryBuilder->getQuery()->getResult();
                    break;
                // Find the relation for the given value and assign to data
                case 'relation':
                    // die($this->getConfig()->mapping->$key->entity);
                    $relation = $this->getObjectManager()->getRepository($this->getConfig()->mapping->$key->entity)
                    ->findOneBy(
                        array(
                        $this->getConfig()->mapping->$key->entity_field_name => $value,
                        )
                    );

                    if (!$relation) {
                        if (isset($this->getConfig()->mapping->$key->allow_null)
                        && $this->getConfig()->mapping->$key->allow_null
                        ) {
                        } else {
                            throw new Exception("Relation was not found: $key: $value");
                        }
                    }

                    if ($relation) {
                        $oAuth2Data[$key] = $value;
                        $doctrineData[$this->getConfig()->mapping->$key->name] = $relation;
                    } else {
                        $oAuth2Data[$key] = null;
                        $doctrineData[$this->getConfig()->mapping->$key->name] = null;
                    }

                    break;
                default:
                    break;
            }
        }

        $this->setOAuth2Data($oAuth2Data);
        $this->setDoctrineData($doctrineData);

        return $this;
    }

    /**
     * Pass data formatted for the oauth2 server
     * and populate both oauth2 and doctrine data
     */
    public function exchangeDoctrineArray(array $array)
    {
        $oAuth2Data = $this->getOAuth2Data();
        $doctrineData = $this->getDoctrineData();
        $config = $this->getConfig();

        foreach ($array as $doctrineKey => $value) {
            // Find the field config key from doctrine field name
            $key = '';

            foreach ($this->getConfig()->mapping as $oAuth2FieldName => $oAuth2Config) {
                if ($oAuth2Config->name == $doctrineKey) {
                    $key = $oAuth2FieldName;
                    break;
                }
            }

            if (!$key) {
                continue;
            }

            switch ($this->getConfig()->mapping->$key->type) {
                // Set the value in data
                case 'field':
                    switch ($this->getConfig()->mapping->$key->datatype) {
                        case 'datetime':
                            // Dates coming from Doctrine are datetimes
                            $oAuth2Data[$key] = $value->format('U');
                            $doctrineData[$this->getConfig()->mapping->$key->name] = $value;
                            break;
                        case 'boolean':
                            $oAuth2Data[$key] = (int) $value;
                            $doctrineData[$this->getConfig()->mapping->$key->name] = (bool) $value;
                            break;
                        default:
                            $oAuth2Data[$key] = $value;
                            $doctrineData[$this->getConfig()->mapping->$key->name] = $value;
                            break;
                    }
                    break;
                case 'collection':
                    $oAuth2String = array();
                    foreach ($value as $entity) {
                        $mapper = $this->getMapperManager()->get($this->getConfig()->mapping->$key->mapper);

                        $mapper->exchangeDoctrineArray($entity->getArrayCopy());
                        $data = $mapper->getOAuth2ArrayCopy();

                        $oAuth2String[] = $data[$this->getConfig()->mapping->$key->name];
                    }
                    $oAuth2Data[$key] = implode(' ', $oAuth2String);
                    $doctrineData[$this->getConfig()->mapping->$key->name] = $value;
                    break;
                // Find the relation for the given value and assign to data
                case 'relation':
                    $entity = $this->getConfig()->mapping->$key->entity;

                    if ($value instanceof $entity) {
                        $relation = $value;
                        $doctrineArray = $relation->getArrayCopy();
                        $oAuth2Value = $doctrineArray[$this->getConfig()->mapping->$key->entity_field_name];
                    } else {
                        $relation = $this->getObjectManager()
                        ->getRepository($this->getConfig()->mapping->$key->entity)
                        ->findOneBy(
                            [
                            $this->getConfig()->mapping->$key->entity_field_name => $value,
                                ]
                        );
                    }

                    if (!$relation) {
                        if (isset($this->getConfig()->mapping->$key->allow_null)
                        && $this->getConfig()->mapping->$key->allow_null
                        ) {
                        } else {
                            throw new Exception(
                                "Null value found for " . $key . " in mapper.  Should the reference be allow_null?"
                            );
                        }
                    }

                    if ($relation) {
                        $oAuth2Data[$key] = $oAuth2Value;
                        $doctrineData[$this->getConfig()->mapping->$key->name] = $relation;

                        // Recursively map relation data.  This should handle the user_id
                        // whenever the client_id is included.
                        foreach ($this->getMapperManager()->getAll() as $mapper) {
                            $entityClass = $mapper->getConfig()->entity;
                            if ($relation instanceof $entityClass) {
                                foreach ($mapper->getConfig()->mapping as $oAuth2Field => $mapperFieldConfig) {
                                    if ($mapperFieldConfig->type == 'relation') {
                                        $foundRecursiveMapping = true;
                                        $doctrineData = $relation->getArrayCopy();
                                        $recursiveEntity = $doctrineData[$mapperFieldConfig->name];

                                        if ($recursiveEntity) {
                                            $recursiveEntityData = $recursiveEntity->getArrayCopy();
                                            $oAuth2Data[$oAuth2Field] =
                                            $recursiveEntityData[$mapperFieldConfig->entity_field_name];

                                            $doctrineData[$mapperFieldConfig->name] = $recursiveEntity;
                                        }

                                        $doctrineData[$mapperFieldConfig->name] = $recursiveEntity;
                                    }
                                }
                            }
                        }


                        // If the relation entity is the dynamically mapped client entity then
                    } else {
                        $oAuth2Data[$key] = null;
                        $doctrineData[$this->getConfig()->mapping->$key->name] = null;
                    }
                    break;
                default:
                    break;
            }
        }

        $this->setOAuth2Data($oAuth2Data);
        $this->setDoctrineData($doctrineData);

        return $this;
    }

    public function getOAuth2ArrayCopy()
    {
        return $this->getOAuth2Data();
    }

    public function getDoctrineArrayCopy()
    {
        return $this->getDoctrineData();
    }
}
